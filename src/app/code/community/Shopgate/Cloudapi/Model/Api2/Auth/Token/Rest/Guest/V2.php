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

class Shopgate_Cloudapi_Model_Api2_Auth_Token_Rest_Guest_V2 extends Shopgate_Cloudapi_Model_Api2_Auth_Rest
{
    /** @noinspection PhpHierarchyChecksInspection */
    /**
     * Creates access_tokens based on grant_type in body
     *
     * @param array $data - incoming request parameters from body
     *
     * @return array
     * @throws LogicException
     * @throws InvalidArgumentException
     */
    public function _create(array $data)
    {
        $server = Mage::getModel('shopgate_cloudapi/oAuth2_server')->initialize($this->_getStore());
        /** @var \OAuth2\Response $response */
        $response = $server->handleTokenRequest(OAuth2\Request::createFromGlobals());
        Mage::helper('shopgate_cloudapi/oAuth2_response')->translate($response);

        return $response->getParameters();
    }
}
