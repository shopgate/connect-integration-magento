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

class Shopgate_Cloudapi_Model_OAuth2_Server
{
    /** Refresh token live time one year */
    const TOKEN_LIFETIME_ONE_YEAR   = '31536000';
    const TOKEN_LIFETIME_ONE_MINUTE = '60';

    /**
     * Initializes the OAuth2 server to be used for
     * token creation and authentication
     *
     * @param Mage_Core_Model_Store $store
     *
     * @return \OAuth2\Server
     */
    public function initialize(Mage_Core_Model_Store $store)
    {
        $resource = Mage::getSingleton('core/resource');
        /** @var Magento_Db_Adapter_Pdo_Mysql $writeConnection */
        $writeConnection = $resource->getConnection('core_write');

        /** @var Shopgate_Cloudapi_Model_OAuth2_Db_Pdo $storage */
        $storage = Mage::getModel('shopgate_cloudapi/oAuth2_db_pdo', array($writeConnection->getConnection()));
        $storage->setStore($store);

        return new OAuth2\Server(
            $storage,
            $this->getConfig(),
            $this->getGrantTypes($storage),
            $this->getResponseTypes($storage)
        );
    }

    /**
     * Retrieve all needed grant types
     *
     * @param \Shopgate\OAuth2\Storage\Pdo $storage
     *
     * @return array
     */
    protected function getGrantTypes(\Shopgate\OAuth2\Storage\Pdo $storage)
    {
        return array(
            new \OAuth2\GrantType\ClientCredentials($storage),
            new \OAuth2\GrantType\UserCredentials($storage),
            new \OAuth2\GrantType\RefreshToken($storage, array('unset_refresh_token_after_use' => false)),
            new \OAuth2\GrantType\AuthorizationCode($storage)
        );
    }

    /**
     * Used to create auth code from observer
     *
     * @param \Shopgate\OAuth2\Storage\Pdo $storage
     *
     * @return array
     */
    protected function getResponseTypes(\Shopgate\OAuth2\Storage\Pdo $storage)
    {
        return array(
            'code' => new OAuth2\ResponseType\AuthorizationCode(
                $storage,
                array('auth_code_lifetime' => self::TOKEN_LIFETIME_ONE_MINUTE)
            )
        );
    }

    /**
     * Retrieves server specific configurations
     *
     * @return array
     */
    protected function getConfig()
    {
        return array(
            'refresh_token_lifetime' => self::TOKEN_LIFETIME_ONE_YEAR
        );
    }
}
