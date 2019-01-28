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
    const FIELD_PSW     = 'password';
    const FIELD_OLD_PSW = 'oldPassword';
    /** @var Mage_Customer_Model_Customer|null */
    private $customer;

    /**
     * Validates the incoming password data
     *
     * @inheritdoc
     * @throws Zend_Validate_Exception
     */
    public function isValidData(array $data, $partial = false)
    {
        if (empty($data[self::FIELD_OLD_PSW])) {
            $this->addDetailedError(Mage::helper('customer')->__('The password cannot be empty.'), self::FIELD_OLD_PSW);
        }

        $customer = $this->getCustomer();
        // check old password
        if (!$customer->validatePassword($data[self::FIELD_OLD_PSW])) {
            $this->addDetailedError(Mage::helper('customer')->__('Invalid current password'), self::FIELD_OLD_PSW);
        }

        //check new password
        $customer->setPassword($data[self::FIELD_PSW])
                 ->setData('password_confirmation', $data[self::FIELD_PSW]);
        $errors = method_exists($customer, 'validateResetPassword')
            ? $customer->validateResetPassword()
            : $this->validateResetPassword($customer);
        if (is_array($errors)) {
            $this->addDetailedErrors($errors, self::FIELD_PSW);
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

    /**
     * Validate customer password on reset
     * Legacy support for version ~CE1.8, for older versions:
     *
     * @see Mage_Customer_Model_Customer::validateResetPassword()
     *
     * @param Mage_Customer_Model_Customer $customer
     *
     * @return array|bool
     * @throws Zend_Validate_Exception
     */
    public function validateResetPassword(Mage_Customer_Model_Customer $customer)
    {
        $errors    = array();
        $helper    = Mage::helper('customer');
        $minLength = 6;
        $maxLength = 256;
        $password  = $customer->getData(self::FIELD_PSW);
        if (!Zend_Validate::is($password, 'NotEmpty')) {
            $errors[] = $helper->__('The password cannot be empty.');
        }
        if (!Zend_Validate::is($password, 'StringLength', array($minLength))) {
            $errors[] = $helper->__('The minimum password length is %s', $minLength);
        }
        if (!Zend_Validate::is($password, 'StringLength', array('max' => $maxLength))) {
            $errors[] = $helper->__('Please enter a password with at most %s characters.', $maxLength);
        }

        return empty($errors) ? : $errors;
    }
}
