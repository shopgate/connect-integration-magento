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

class Shopgate_Cloudapi_Model_Api2_Customers_Password_Validator extends Shopgate_Cloudapi_Model_Api2_Validator
{
    const FIELD_NEW_PSW = 'password';
    const FIELD_OLD_PSW = 'oldPassword';
    /** @var Mage_Customer_Model_Customer|null */
    private $customer;

    /**
     * Validates the incoming password data
     *
     * @inheritdoc
     */
    public function isValidData(array $data, $partial = false)
    {
        if (empty($data['oldPassword'])) {
            $this->addDetailedError(Mage::helper('customer')->__('The password cannot be empty.'), self::FIELD_OLD_PSW);
        }

        $customer = $this->getCustomer();
        // check old password
        if (!$customer->validatePassword($data['oldPassword'])) {
            $this->addDetailedError(Mage::helper('customer')->__('Invalid current password'), self::FIELD_OLD_PSW);
        }

        //check new password
        $customer->setPassword($data['password'])->setData('password_confirmation', $data['password']);
        $errors = $customer->validateResetPassword();
        if (is_array($errors)) {
            $this->addDetailedErrors($errors, self::FIELD_NEW_PSW);
        }

        return count($this->getDetailedErrors()) === 0;
    }

    /**
     * @param Mage_Customer_Model_Customer $customer
     *
     * @return Shopgate_Cloudapi_Model_Api2_Customers_Password_Validator
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
