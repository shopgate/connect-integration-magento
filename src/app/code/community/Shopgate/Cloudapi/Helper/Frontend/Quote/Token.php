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

class Shopgate_Cloudapi_Helper_Frontend_Quote_Token extends Mage_Core_Helper_Abstract
{
    /**
     * Retrieve quote by token
     *
     * @param string $token
     *
     * @return Mage_Sales_Model_Quote
     * @throws Shopgate_Cloudapi_Model_Frontend_Checkout_Exception
     */
    public function getQuoteByToken($token)
    {
        /** @var Magento_Db_Adapter_Pdo_Mysql $writeConnection */
        $writeConnection = Mage::getSingleton('core/resource')->getConnection('core_write');
        /** @var Shopgate_Cloudapi_Model_OAuth2_Db_Pdo $storage */
        $storage  = Mage::getModel('shopgate_cloudapi/oAuth2_db_pdo', array($writeConnection->getConnection()));
        $authCode = $storage->getAuthItemByTokenAndType($token, \Shopgate\OAuth2\Storage\Pdo::AUTH_TYPE_CHECKOUT);

        if ($authCode->getIsExpired()) {
            $this->getCheckoutHelper()->throwException('Cart link has expired');
        }

        $quote = Mage::getModel('sales/quote')->loadActive($authCode->getResourceId());
        if (!$quote->getId()) {
            $this->getCheckoutHelper()->throwException('Link provided does not match any cart');
        }
        $orderIncrementId = $quote->getData('reserved_order_id');
        if ($orderIncrementId && Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId)->getData()) {
            $this->getCheckoutHelper()->throwException('Cart by this link was already purchased');
        }

        return $quote;
    }

    /**
     * Retrieves the checkout helper
     *
     * @return Shopgate_Cloudapi_Helper_Frontend_Checkout
     */
    public function getCheckoutHelper()
    {
        return Mage::helper('shopgate_cloudapi/frontend_checkout');
    }
}
