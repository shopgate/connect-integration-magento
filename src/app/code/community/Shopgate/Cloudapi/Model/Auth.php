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

class Shopgate_Cloudapi_Model_Auth
{

    /**
     * Validate call and create user model instance
     *
     * @param Mage_Api2_Model_Request $request
     *
     * @return Mage_Api2_Model_Auth_User_Abstract
     * @throws Mage_Core_Model_Store_Exception
     * @throws Mage_Api2_Exception
     * @throws Mage_Core_Exception
     */
    public function authenticate(Mage_Api2_Model_Request $request)
    {
        /**
         * Forwards to endpoint directly as there is no need to validate this request
         */
        if (strpos($request->getPathInfo(), '/auth/token') !== false) {
            return $this->retrieveUser(Mage_Api2_Model_Auth_User_Guest::USER_TYPE);
        }

        $store = Mage::app()->getStore($request->getParam('store'));
        $email = $this->validateRequest($store);
        if (null !== $email) {
            //todo-sg: maybe pull store from client_id instead? Need to verify that the shop_number store === current?
            $customer = Mage::getModel('customer/customer')->setStore($store)->loadByEmail($email);

            return $this->retrieveUser(Mage_Api2_Model_Auth_User_Customer::USER_TYPE, $customer->getId());
        }

        $adminName = Shopgate_Cloudapi_Model_Resource_Setup::SG_ADMIN_USERNAME;
        $userId    = Mage::getModel('admin/user')->loadByUsername($adminName)->getId();

        return $this->retrieveUser(Mage_Api2_Model_Auth_User_Admin::USER_TYPE, $userId);
    }

    /**
     * Loads the user type given
     *
     * @param string          $type - possible values 'guest', 'customer', 'admin'
     * @param null|string|int $userId
     *
     * @return Mage_Api2_Model_Auth_User_Admin | Mage_Api2_Model_Auth_User_Customer | Mage_Api2_Model_Auth_User_Guest
     * @throws Mage_Core_Exception
     */
    protected function retrieveUser($type, $userId = null)
    {
        $user = Mage::getModel('api2/auth_user');
        if (!array_key_exists($type, $user::getUserTypes())) {
            Mage::throwException('User type "%s" not found.', $type);
        }

        $user = Mage::getModel('api2/auth_user_' . $type);
        if ($type !== Mage_Api2_Model_Auth_User_Guest::USER_TYPE) {
            /** @var Mage_Api2_Model_Auth_User_Admin | Mage_Api2_Model_Auth_User_Customer $user */
            $user->setUserId($userId);
        }
        $user->getRole();

        return $user;
    }

    /**
     * Verifies request access_token authenticity
     *
     * @param Mage_Core_Model_Store $store
     *
     * @return null | string - customer email address or null if token belongs to a guest
     * @throws Mage_Api2_Exception
     */
    protected function validateRequest(Mage_Core_Model_Store $store)
    {
        $server = Mage::getModel('shopgate_cloudapi/oAuth2_server')->initialize($store);
        if (!$server->verifyResourceRequest(OAuth2\Request::createFromGlobals())) {
            $response = $server->getResponse();
            Mage::helper('shopgate_cloudapi/oAuth2_response')->translate($response);
            throw new Mage_Api2_Exception($response->getStatusText(), $response->getStatusCode());
        }

        $token = $server->getResourceController()->getToken();

        return $token['user_id'];
    }
}
