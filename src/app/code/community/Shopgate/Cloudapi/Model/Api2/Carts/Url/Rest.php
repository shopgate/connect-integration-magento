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

abstract class Shopgate_Cloudapi_Model_Api2_Carts_Url_Rest extends Shopgate_Cloudapi_Model_Api2_Carts_Utility
{
    /** @noinspection PhpHierarchyChecksInspection */
    /**
     * @param array $filteredData
     *
     * @return array
     * @throws Mage_Core_Exception
     */
    protected function _create(array $filteredData)
    {
        return $this->_createUrl($this->loadUserQuote());
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     *
     * @return array
     * @throws Mage_Core_Exception
     */
    protected function _createUrl($quote)
    {
        /** @var Shopgate_Cloudapi_Helper_Frontend_Checkout $helper */
        $helper = Mage::helper('shopgate_cloudapi/frontend_checkout');
        $token  = $helper->generateAuthToken($quote->getId(), $this->_getStore(), $quote->getCustomerId());
        $url    = Mage::getUrl(
            Shopgate_Cloudapi_Helper_Frontend_Checkout::AUTH_CHECKOUT_URL,
            array(
                'token' => $token->getAuthorizationCode(),
                '_store' => $this->_getStore()
            )
        );

        return array(
            'url'        => $url,
            'expires_in' => \Shopgate\OAuth2\Storage\Pdo::AUTH_TOKEN_EXPIRE_SECONDS
        );
    }

    /**
     * Loads user quote
     *
     * @return Mage_Sales_Model_Quote
     */
    abstract protected function loadUserQuote();
}
