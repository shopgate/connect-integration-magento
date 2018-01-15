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

class Shopgate_Cloudapi_Model_Api2_Carts_Utility extends Shopgate_Cloudapi_Model_Api2_Resource
{
    /**
     * @throws Mage_Api2_Exception
     * @throws Exception
     */
    protected function validateGuestCartId()
    {
        $cartId = $this->getRequest()->getParam('cartId');
        if ($this->isMeEndpoint()) {
            $this->_critical('ME endpoint is not allowed for guests', Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        } elseif (!is_numeric($cartId)) {
            $this->_critical('Invalid URL parameter provided: ' . $cartId, Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @throws Mage_Api2_Exception
     * @throws Exception
     */
    protected function validateCustomerCartId()
    {
        $cartId = $this->getRequest()->getParam('cartId');
        if (!(is_numeric($cartId) || $this->isMeEndpoint())) {
            $this->_critical('Invalid URL parameter provided: ' . $cartId, Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Checks if the path is a ME endpoint
     *
     * @return bool
     * @throws Exception
     */
    protected function isMeEndpoint()
    {
        return 'me' === $this->getRequest()->getParam('cartId');
    }

    /**
     * Loads quote by provided ID
     *
     * @param string $quoteId - numeric quote ID
     *
     * @return Mage_Sales_Model_Quote
     * @throws Mage_Api2_Exception
     */
    protected function loadQuoteById($quoteId)
    {
        return Mage::getModel('sales/quote')
                   ->setStore($this->_getStore())
                   ->load($quoteId);
    }

    /**
     * Loads the quote using the customer provided
     *
     * @param null | bool $validate - whether to validate quote after load
     *
     * @throws Exception
     * @return Mage_Sales_Model_Quote
     */
    protected function loadQuoteByCustomer($validate = null)
    {
        $quote = Mage::getModel('sales/quote')
                     ->setStore($this->_getStore())
                     ->loadByCustomer($this->getApiUser()->getUserId());

        $quote = $quote->getId() ? $quote : $this->loadInactiveCustomerQuote();

        if (!$quote->getId()) {
            $quote = $this->createNewCustomerQuote($this->getApiUser()->getUserId());
        }

        if ($validate) {
            $this->validateCustomerQuote($quote);
        }

        return $quote;
    }

    /**
     * Checks that the guest quote is loaded
     *
     * @param Mage_Sales_Model_Quote $quote
     *
     * @throws Mage_Api2_Exception
     */
    protected function validateGuestQuote(Mage_Sales_Model_Quote $quote)
    {
        if (!$quote->getId()) {
            $this->_critical('Cart not found', Mage_Api2_Model_Server::HTTP_NOT_FOUND);
        } elseif ($quote->getCustomerId()) {
            $this->_critical('A user cart was loaded', Mage_Api2_Model_Server::HTTP_FORBIDDEN);
        }
    }

    /**
     * Checks that the proper customer quote is loaded
     *
     * @param Mage_Sales_Model_Quote $quote
     *
     * @throws Mage_Api2_Exception
     * @throws Exception
     */
    protected function validateCustomerQuote(Mage_Sales_Model_Quote $quote)
    {
        if (!$quote->getId()) {
            $this->_critical('Cart not found', Mage_Api2_Model_Server::HTTP_NOT_FOUND);
        } elseif ($this->getApiUser()->getUserId() !== $quote->getCustomerId()) {
            $this->_critical('Cart does not belong to this customer', Mage_Api2_Model_Server::HTTP_FORBIDDEN);
        }
    }

    /**
     * Returns the last quote of the customer if he has no
     * quote that is currently active.
     *
     * @return Mage_Sales_Model_Quote
     * @throws Exception
     */
    private function loadInactiveCustomerQuote()
    {
        /** @var Mage_Sales_Model_Quote $quote */
        $quote = Mage::getResourceModel('sales/quote_collection')
                     ->addFieldToSelect('*')
                     ->addFieldToFilter('customer_id', $this->getApiUser()->getUserId())
                     ->setOrder('entity_id', Varien_Data_Collection::SORT_ORDER_DESC)
                     ->setPageSize(1)
                     ->getFirstItem();

        return $quote;
    }

    /**
     * Creates a new quote for customerId and sets it to active
     *
     * @param int $customerId
     * @return Mage_Sales_Model_Quote
     */
    private function createNewCustomerQuote($customerId)
    {
        $quoteId = Mage::getModel('checkout/cart_api')->create($this->_getStore());
        /** @var Mage_Sales_Model_Quote $quote */
        $quote = Mage::getModel('sales/quote')
                     ->setStore($this->_getStore())
                     ->load($quoteId);

        $quote->getShippingAddress()->setCollectShippingRates(true);
        $quote->setCustomerId($customerId)
              ->setIsActive(1)
              ->save();

        return $quote;
    }
}
