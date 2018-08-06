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

abstract class Shopgate_Cloudapi_Model_Api2_Carts_Rest extends Shopgate_Cloudapi_Model_Api2_Carts_Utility
{
    const FAULT_MODULE   = 'Mage_Checkout';
    const FAULT_RESOURCE = 'cart';

    /** @noinspection PhpHierarchyChecksInspection */
    /**
     * @inheritdoc
     * @throws Mage_Api2_Exception
     */
    public function _create(array $filteredData)
    {
        $this->deactivateUserQuote();
        try {
            $quoteId = (int)$this->createNewQuote()->getId();
        } catch (Mage_Api_Exception $e) {
            $error = $this->getFault($e->getMessage(), 'Fault: ' . $e->getMessage() . ' ' . $e->getCustomMessage());
            $this->_critical($error, Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        } catch (Exception $e) {
            $this->_critical($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }

        /** @noinspection PhpUndefinedVariableInspection */
        return array('cartId' => $quoteId);
    }

    /** @noinspection PhpHierarchyChecksInspection */
    /**
     * Retrieve user's cart data
     *
     * @return array
     * @throws Mage_Core_Model_Store_Exception
     * @throws Zend_Currency_Exception
     */
    protected function _retrieve()
    {
        $quote       = $this->getUserQuote();
        /* @var Shopgate_Cloudapi_Helper_Api2_Quote $quoteHelper */
        $quoteHelper = Mage::helper('shopgate_cloudapi/api2_quote');
        $quoteHelper->setSaleRuleType($quote, $this->_getStore());
        $quoteHelper->addQuoteErrors($quote, $this->_getStore());
        $quoteHelper->addItemErrors($quote, $this->_getStore());
        $quoteHelper->addItems($quote);
        $quoteHelper->addTotals($quote);
        $quoteHelper->addCartPriceDisplaySettings($quote, $this->_getStore());

        return $quote->getData();
    }

    /**
     * Abandon current user's quote
     */
    protected function deactivateUserQuote()
    {
        $quote = $this->getUserOldQuote();
        if ($quote->getId() && (int)$quote->getIsActive() === 1) {
            $quote->setIsActive(0)
                  ->save();
        }
    }

    /**
     * Creates a new quote and sets it to active
     *
     * @return Mage_Sales_Model_Quote
     */
    protected function createNewQuote()
    {
        $quoteId = Mage::getModel('checkout/cart_api')->create($this->_getStore());
        /** @var Mage_Sales_Model_Quote $quote */
        $quote = Mage::getModel('sales/quote')
                     ->setStore($this->_getStore())
                     ->load($quoteId);

        $quote->getShippingAddress()->setCollectShippingRates(true);
        $quote->setCustomerId(null)
              ->setIsActive(1)
              ->save();

        return $quote;
    }

    /**
     * Returns the current user's quote
     *
     * @return Mage_Sales_Model_Quote
     */
    abstract protected function getUserOldQuote();

    /**
     * Loads user quote
     *
     * @return Mage_Sales_Model_Quote
     */
    abstract protected function getUserQuote();
}
