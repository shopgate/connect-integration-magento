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

/**
 * @method string getAuthorizationCode() - retrieve unique code
 * @method Shopgate_Cloudapi_Model_Auth_Code setAuthorizationCode(string $token) - set unique code
 * @method string getClientId() - retrieve client ID
 * @method Shopgate_Cloudapi_Model_Auth_Code setClientId(string $clientId) - set client ID
 * @method string getUserId() - user that is associated to the resource
 * @method Shopgate_Cloudapi_Model_Auth_Code setUserId(string $userId) - user that is associated to the resource
 * @method string getRedirectUri() - returns redirect URI
 * @method Shopgate_Cloudapi_Model_Auth_Code setRedirectUri(string $uri) - set redirect URI
 * @method string getExpires() - resource expiration timestamp
 * @method Shopgate_Cloudapi_Model_Auth_Code setExpires(string $date) - resource expiration timestamp
 * @method string getScope() - scope of authorization
 * @method Shopgate_Cloudapi_Model_Auth_Code setScope(string $scope) - scope of authorization
 * @method string getIdToken()
 * @method Shopgate_Cloudapi_Model_Auth_Code setIdToken(string $idToken)
 * @method string getResourceId() - resource identification, e.g. quote ID
 * @method Shopgate_Cloudapi_Model_Auth_Code setResourceId(string $quoteId) - resource identification, e.g. quote ID
 * @method string getResourceType() - type of resource, e.g. checkout
 * @method Shopgate_Cloudapi_Model_Auth_Code setResourceType(string $type) - type of resource, e.g. checkout
 */
class Shopgate_Cloudapi_Model_Auth_Code extends Mage_Core_Model_Abstract
{

    const KEY_IS_EXPIRED = 'is_expired';

    /**
     * Init model
     */
    protected function _construct()
    {
        $this->_init('shopgate_cloudapi/auth_code');
    }

    /**
     * @return bool
     */
    public function isExpired()
    {
        $currentDate = new DateTime($this->getCoreDateModel()->date());
        $compareDate = new DateTime($this->getExpires());
        $diffSeconds = $currentDate->getTimestamp() - $compareDate->getTimestamp();

        return $diffSeconds > \Shopgate\OAuth2\Storage\Pdo::AUTH_TOKEN_EXPIRE_SECONDS;
    }

    /**
     * @return bool | null
     */
    public function getIsExpired()
    {
        if (!$this->hasData(self::KEY_IS_EXPIRED)) {
            $this->setData(self::KEY_IS_EXPIRED, $this->isExpired());
        }

        return $this->getData(self::KEY_IS_EXPIRED);
    }

    /**
     * @return false|Mage_Core_Model_Date
     */
    public function getCoreDateModel()
    {
        return Mage::getModel('core/date');
    }
}
