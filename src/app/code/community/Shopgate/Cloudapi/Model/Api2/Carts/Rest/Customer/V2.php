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

class Shopgate_Cloudapi_Model_Api2_Carts_Rest_Customer_V2 extends Shopgate_Cloudapi_Model_Api2_Carts_Rest
{
    /**
     * Pulls newly created quote ID and assigns the
     * current user to it
     *
     * @inheritdoc
     * @throws Mage_Api2_Exception
     * @throws Exception
     */
    protected function createNewQuote()
    {
        $quote = parent::createNewQuote();
        if ($quote->getId()) {
            $quote->setCustomerId($this->getApiUser()->getUserId());
            $quote = $this->getQuoteCustomerHelper()->setCustomerData($quote);
            $quote->save();
        } else {
            $this->_critical('Could not assign customer to quote', Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }

        return $quote;
    }

    /**
     * Retrieves the customer quote using the user ID
     * of the loaded ACL user. No need to validate quote
     * as a new customer may not have any.
     *
     * @inheritdoc
     * @throws Exception
     */
    protected function getUserOldQuote()
    {
        return $this->loadQuoteByCustomer(false);
    }

    /**
     * Retrieves the current user active quote
     * disregarding URI quote ID
     *
     * @return Mage_Sales_Model_Quote
     * @throws Exception
     */
    protected function getUserQuote()
    {
        $this->validateCustomerCartId();

        $quoteId = $this->getRequest()->getParam('cartId');
        $quote   = $this->isMeEndpoint() ? $this->loadQuoteByCustomer() : $this->loadQuoteById($quoteId);
        $this->validateCustomerQuote($quote);

        return $quote;
    }

    /**
     * @return Shopgate_Cloudapi_Helper_Frontend_Quote_Customer
     */
    protected function getQuoteCustomerHelper()
    {
        return Mage::helper('shopgate_cloudapi/frontend_quote_customer');
    }
}
