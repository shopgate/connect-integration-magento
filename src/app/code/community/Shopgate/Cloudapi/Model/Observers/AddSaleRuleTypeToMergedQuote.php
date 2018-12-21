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
class Shopgate_Cloudapi_Model_Observers_AddSaleRuleTypeToMergedQuote
{
    /**
     * Transfers the flag for app-only discounts from a guest cart to the customer cart
     *
     * @param Varien_Event_Observer $observer
     */
    public function execute(Varien_Event_Observer $observer)
    {
        $oldQuoteId = $observer->getData('source')->getId();
        $newQuoteId = $observer->getData('quote')->getId();
        $collection = Mage::getResourceModel('shopgate_cloudapi/cart_source_collection')->setQuoteFilter($oldQuoteId);
        if (!$collection->isEmpty()) {
            $collection = Mage::getResourceModel('shopgate_cloudapi/cart_source_collection')->setQuoteFilter($newQuoteId);
            if ($collection->isEmpty()) {
                Mage::getModel('shopgate_cloudapi/cart_source')->saveQuote($newQuoteId);
            }
        }
    }
}
