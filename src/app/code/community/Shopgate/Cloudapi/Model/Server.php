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

class Shopgate_Cloudapi_Model_Server extends Mage_Api2_Model_Server
{

    /**
     * We do Shopgate OAuth2 authentication here and user loading by token
     *
     * @inheritdoc
     */
    protected function _authenticate(Mage_Api2_Model_Request $request)
    {
        /** @var $authManager Shopgate_Cloudapi_Model_Auth */
        $authManager = Mage::getModel('shopgate_cloudapi/auth');
        $this->_setAuthUser($authManager->authenticate($request));

        return $this->_getAuthUser();
    }

    /**
     * Rewrite to use our own dispatcher.
     * This is done initially because we needed to
     * manipulate the Header Version via URL parameter.
     *
     * @inheritdoc
     */
    protected function _dispatch(
        Mage_Api2_Model_Request $request,
        Mage_Api2_Model_Response $response,
        Mage_Api2_Model_Auth_User_Abstract $apiUser
    ) {
        $dispatcher = Mage::getModel('shopgate_cloudapi/api2_dispatcher');
        $dispatcher->setApiUser($apiUser)->dispatch($request, $response);

        return $this;
    }
}
