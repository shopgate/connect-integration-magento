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

class Shopgate_Cloudapi_Helper_OAuth2_Response extends Mage_Core_Helper_Abstract
{
    /**
     * Translates OAuth2 response into Api2_Response
     *
     * @param \OAuth2\Response $response
     *
     * @return Mage_Api2_Model_Response
     */
    public function translate(\OAuth2\Response $response)
    {
        $apiResponse = Mage::getSingleton('api2/response');
        foreach ($response->getHttpHeaders() as $name => $value) {
            $apiResponse->setHeader($name, $value, true);
        }

        $apiResponse->setHttpResponseCode($response->getStatusCode());

        return $apiResponse;
    }
}
