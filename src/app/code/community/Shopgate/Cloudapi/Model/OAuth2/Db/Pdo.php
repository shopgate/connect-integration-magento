<?php

/**
 * Copyright Shopgate Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @author    Shopgate Inc, 804 Congress Ave, Austin, Texas 78701 <interfaces@shopgate.com>
 * @copyright Shopgate Inc
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

class Shopgate_Cloudapi_Model_OAuth2_Db_Pdo extends \Shopgate\OAuth2\Storage\Pdo
{
    const KEY_RESOURCE_ID = 'resource_id';

    /**
     * @var Mage_Core_Model_Store
     */
    protected $store;

    /**
     * Rewritten due to how magento does constructor parameter passing
     *
     * @param array $connection - array(PDO connection, database config)
     * @param array $config     - will be certainly empty
     *
     * @throws \Mage_Core_Exception
     */
    public function __construct($connection, $config = array())
    {
        if (is_array($connection) && !isset($connection['dsn'])) {
            if (count($connection) === 1) {
                parent::__construct($connection[0], $this->getTables());
            } elseif (count($connection) === 2) {
                parent::__construct($connection[0], array_merge($connection[1], $this->getTables()));
            } else {
                Mage::throwException('Too many parameters passed to the constructor');
            }
        } else {
            Mage::throwException('Unexpected Storage initialization');
        }
    }

    /**
     * @param Mage_Core_Model_Store $store
     *
     * @return $this
     */
    public function setStore(Mage_Core_Model_Store $store)
    {
        $this->store = $store;

        return $this;
    }

    /**
     * Cross checks with Magento's password
     *
     * @param array  $user - array['password' => :db_hash, 'user_id' => :db_email]
     * @param string $password
     *
     * @return bool
     */
    protected function checkPassword($user, $password)
    {
        return Mage::helper('core')->validateHash($password, $user['password']);
    }

    /**
     * Retrieves the uses by email, false if it cannot find one
     *
     * @param string $username - email address
     *
     * @return array|false
     * @throws \Mage_Core_Exception
     */
    public function getUser($username)
    {
        if (!$this->store) {
            Mage::throwException('Cannot retrieve user to authenticate, store is not set');
        }
        /** @var Mage_Customer_Model_Customer $customer */
        $customer = Mage::getModel('customer/customer')->setStore($this->store)->loadByEmail($username);

        return $customer->getId() ? array(
            'user_id'  => $customer->getData('entity_id'),
            'password' => $customer->getData('password_hash')
        ) : false;
    }

    /**
     * Generic info as we do not need to redirect
     *
     * @inheritdoc
     * @throws \Mage_Core_Exception
     */
    public function getClientDetails($client_id)
    {
        return $this->getClientId() ? array('redirect_uri' => 'http://www.shopgate.com') : false;
    }

    /**
     * Cross checks credentials on auth/token call
     *
     * @param string $client_id     - PHP_AUTH_USER or Body: client_id
     * @param string $client_secret - PHP_AUTH_PW or Body: client_secret
     *
     * @return bool
     * @throws \Mage_Core_Exception
     */
    public function checkClientCredentials($client_id, $client_secret = null)
    {
        if (!$this->store) {
            Mage::throwException('Cannot retrieve client credentials, store is not set');
        }

        return $this->getClientId() === $client_id && $this->getClientSecret() === $client_secret;
    }

    /**
     * Do not use scopes
     *
     * @inheritdoc
     */
    public function getDefaultScope($client_id = null)
    {
        return null;
    }

    /**
     * Retrieves the client_id from the configurations
     *
     * @return string - [customer_number]-[shop_number]
     * @throws Mage_Core_Exception
     */
    public function getClientId()
    {
        $store    = $this->store;
        $customer = Mage::getStoreConfig(Shopgate_Cloudapi_Helper_Data::PATH_AUTH_CUSTOMER_NUMBER, $store);
        $shop     = Mage::getStoreConfig(Shopgate_Cloudapi_Helper_Data::PATH_AUTH_SHOP_NUMBER, $store);

        if (!$customer || !$shop) {
            Mage::throwException('Could not generate the client_id, please configure the Shopgate module');
        }

        return $customer . '-' . $shop;
    }

    /**
     * Pulls the API key as the client_secret from the database configuration
     *
     * @return string
     * @throws Mage_Core_Exception
     */
    public function getClientSecret()
    {
        $secret = Mage::getStoreConfig(Shopgate_Cloudapi_Helper_Data::PATH_AUTH_API_KEY, $this->store);

        if (!$secret) {
            Mage::throwException('Could not retrieve API key, please configure the Shopgate module');
        }

        return $secret;
    }

    /**
     * @inheritdoc
     * @return Shopgate_Cloudapi_Model_Auth_Code
     */
    public function getAuthItemByTokenAndType($authorizationCode, $resourceType)
    {
        $auth = Mage::getModel('shopgate_cloudapi/auth_code');
        $auth->setData(parent::getAuthItemByTokenAndType($authorizationCode, $resourceType));

        return $auth;
    }

    /**
     * Rewrites redirectUri parameter passed into resourceType
     *
     * @inheritdoc
     * @throws Mage_Core_Exception
     */
    public function setAuthorizationCode(
        $code, $clientId, $userId, $resourceType, $expires, $scope = null, $id_token = null
    ) {
        if (func_num_args() > 6) {
            Mage::throwException('OpenID not yet supported');
        }

        // convert expires to date string
        $expires = date('Y-m-d H:i:s', $expires);

        // if it exists, update it.
        if ($this->getAuthorizationCode($code)) {
            $stmt = $this->db->prepare(
                $sql = sprintf(
                    'UPDATE %s SET client_id=:clientId, user_id=:userId, expires=:expires, scope=:scope, resource_type=:resourceType where authorization_code=:code',
                    $this->config['code_table']
                )
            );
        } else {
            $stmt = $this->db->prepare(
                sprintf(
                    'INSERT INTO %s (authorization_code, client_id, user_id, expires, scope, resource_type) VALUES (:code, :clientId, :userId, :expires, :scope, :resourceType)',
                    $this->config['code_table']
                )
            );
        }

        return $stmt->execute(compact('code', 'clientId', 'userId', 'resourceType', 'expires', 'scope'));
    }

    /**
     * Removes expired Access / Refresh token entries from the database
     */
    public function cleanOldEntries()
    {
        $this->removeExpiredAccessTokens();
        $this->removeExpiredRefreshTokens();
        $this->removeExpiredResourceAuthCodes();
    }

    /**
     * Retrieves table configurations to use in OAuth2 database creation.
     * Empty values means that it will not create those tables.
     */
    private function getTables()
    {
        return array(
            'client_table'        => null,
            'access_token_table'  => 'shopgate_oauth_access_tokens',
            'refresh_token_table' => 'shopgate_oauth_refresh_tokens',
            'code_table'          => 'shopgate_oauth_authorization_codes',
            'user_table'          => null,
            'jwt_table'           => null,
            'jti_table'           => null,
            'scope_table'         => null,
            'public_key_table'    => null
        );
    }
}
