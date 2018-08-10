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

class Shopgate_Cloudapi_Helper_Frontend_Quote_Customer extends Mage_Core_Helper_Abstract
{
    /**
     * Helps set data from customer to Quote to facilitate coupon validation
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return Mage_Sales_Model_Quote
     */
    public function setCustomerData(Mage_Sales_Model_Quote $quote)
    {
        $quote->setCustomerGroupId($quote->getCustomer()->getGroupId());
        $quote->setCustomerEmail($quote->getCustomer()->getData('email'));
        $quote->setCustomerFirstname($quote->getCustomer()->getData('firstname'));
        $quote->setCustomerLastname($quote->getCustomer()->getData('lastname'));
        $quote->setCustomerMiddlename($quote->getCustomer()->getData('middlename'));
        $quote->setCustomerDob($quote->getCustomer()->getData('dob'));
        $quote->setCustomerGender($quote->getCustomer()->getData('gender'));
        $quote->setCustomerPrefix($quote->getCustomer()->getData('prefix'));
        $quote->setCustomerSuffix($quote->getCustomer()->getData('suffix'));
        $quote->getCustomerTaxClassId();

        return $quote;
    }
}
