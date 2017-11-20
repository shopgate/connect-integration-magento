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
 * CORS Pre-flight handler, this is a call made by the browser with JavaScript that
 * requests headers to make proper calls.
 */
class Shopgate_Cloudapi_Helper_Preflight extends Mage_Core_Helper_Abstract
{
    /**
     * Checks if this is a CORS Pre-flight call
     *
     * @return bool
     */
    public function isCorsCall()
    {
        $cors = Mage::app()->getRequest()->getHeader('Access-Control-Request-Method');

        return !empty($cors);
    }

    /**
     * Send CORS headers now
     */
    public function sendCorsHeaders()
    {
        foreach ($this->getCorsHeaders() as $header) {
            Mage::app()->getResponse()->setHeader($header->getName(), $header->getValue(), $header->getReplace());
        }
        Mage::app()->getResponse()->sendResponse();
    }

    /**
     * Get a CORS compliant response
     *
     * @return Mage_Core_Controller_Response_Http
     */
    public function getCorsResponse()
    {
        $response = new Mage_Core_Controller_Response_Http();
        foreach ($this->getCorsHeaders() as $header) {
            $response->setHeader($header->getName(), $header->getValue(), $header->getReplace());
        }

        return $response;
    }

    /**
     * Retrieves a list of CORS compliant headers
     *
     * @return array
     */
    private function getCorsHeaders()
    {
        return array(
            new Varien_Object(
                array('name' => 'Access-Control-Allow-Origin', 'value' => '*', 'replace' => true)
            ),
            new Varien_Object(
                array(
                    'name'    => 'Access-Control-Allow-Methods',
                    'value'   => 'GET, POST, UPDATE, DELETE, OPTIONS',
                    'replace' => true
                )
            ),
            new Varien_Object(
                array(
                    'name'    => 'Access-Control-Allow-Headers',
                    'value'   => 'Content-Type,Version,Authorization,PHP_AUTH_USER,PHP_AUTH_PW',
                    'replace' => true
                )
            ),
        );
    }
}
