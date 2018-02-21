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

class Shopgate_Cloudapi_Helper_Frontend_Quote extends Mage_Core_Helper_Abstract
{
    /**
     * @param Mage_Customer_Model_Customer $customer
     * @param Mage_Core_Model_Store        $store
     *
     * @return Mage_Sales_Model_Quote
     * @throws Mage_Core_Model_Store_Exception
     * @throws Exception
     */
    public function createNewCustomerQuote($customer, $store)
    {
        /** @var Mage_Sales_Model_Quote $quote */
        $quote = Mage::getModel('sales/quote')->assignCustomer($customer);
        $quote->setStore($store);
        $quote = $this->getQuoteCustomerHelper()->setCustomerData($quote);
        $quote->save();

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
