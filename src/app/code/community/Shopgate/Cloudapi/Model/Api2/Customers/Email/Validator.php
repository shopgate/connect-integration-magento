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

class Shopgate_Cloudapi_Model_Api2_Customers_Email_Validator extends Shopgate_Cloudapi_Model_Api2_Validator
{
    const FIELD_EMAIL = 'email';
    /** @var Mage_Customer_Model_Customer|null */
    private $customer;

    /**
     * Validates the incoming email address data
     *
     * @inheritdoc
     * @throws Zend_Validate_Exception
     */
    public function isValidData(array $data, $partial = false)
    {
        if (empty($data[self::FIELD_EMAIL])) {
            $this->addDetailedError(
                Mage::helper('customer')->__('The email address cannot be empty.'),
                self::FIELD_EMAIL
            );
        }

        if (!Zend_Validate::is($data[self::FIELD_EMAIL], 'EmailAddress')) {
            $this->addDetailedError(
                Mage::helper('customer')->__('Invalid email address "%s".', $data[self::FIELD_EMAIL]),
                self::FIELD_EMAIL
            );
        }

        $item = Mage::getResourceModel('customer/customer_collection')
                    ->addFieldToFilter('email', $data[self::FIELD_EMAIL])
                    ->addFieldToFilter('website_id', $this->getCustomer()->getData('website_id'))
                    ->addFieldToFilter('entity_id', array('neq' => $this->getCustomer()->getId()))
                    ->getFirstItem();
        if ($item->getId()) {
            $this->addDetailedError(
                Mage::helper('customer')->__('This customer email already exists'),
                self::FIELD_EMAIL
            );
        }

        return count($this->getDetailedErrors()) === 0;
    }

    /**
     * @param Mage_Customer_Model_Customer $customer
     *
     * @return Shopgate_Cloudapi_Model_Api2_Customers_Email_Validator
     */
    public function setCustomer(Mage_Customer_Model_Customer $customer)
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * @return Mage_Customer_Model_Customer
     * @throws Mage_Core_Exception
     */
    public function getCustomer()
    {
        if (null === $this->customer) {
            Mage::throwException('Customer needs to be initialized');
        }

        return $this->customer;
    }
}
