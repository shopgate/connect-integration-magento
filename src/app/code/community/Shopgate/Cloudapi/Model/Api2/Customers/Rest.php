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

class Shopgate_Cloudapi_Model_Api2_Customers_Rest extends Shopgate_Cloudapi_Model_Api2_Resource
{
    /**
     * @inheritdoc
     * @return Shopgate_Cloudapi_Model_Acl_Customers_Filter
     */
    public function getFilter()
    {
        if (!$this->filter) {
            $this->filter = Mage::getModel('shopgate_cloudapi/acl_customers_filter', $this);
        }

        return $this->filter;
    }

    /**
     * Get customer resource validator instance
     *
     * @return Shopgate_Cloudapi_Model_Api2_Validator
     */
    public function getValidator()
    {
        return Mage::getModel('shopgate_cloudapi/api2_validator', array('resource' => $this));
    }

    /**
     * Sets detailed validation errors to be returned by the address endpoints
     *
     * @param Shopgate_Cloudapi_Model_Api2_Validator|Shopgate_Cloudapi_Model_Api2_Customers_Addresses_Validator $validator
     *
     * @return Shopgate_Cloudapi_Model_Api2_Customers_Rest
     */
    protected function setDetailedErrors($validator)
    {
        foreach ($validator->getDetailedErrors() as $code => $errors) {
            $this->_errorMessage(
                '',
                Mage_Api2_Model_Server::HTTP_BAD_REQUEST,
                array('path' => $code, 'messages' => $errors)
            );
        }

        return $this;
    }

    /**
     * Bypasses the exception state and passes down invalidation errors
     *
     * @throws Zend_Controller_Response_Exception
     * @throws Exception
     */
    protected function sendInvalidationResponse()
    {
        $this->getResponse()->setHttpResponseCode(Mage_Api2_Model_Server::HTTP_BAD_REQUEST);

        return array('messages' => $this->getResponse()->getMessages());
    }

    /**
     * Load customer by id
     *
     * @param string|int $id
     *
     * @throws Mage_Api2_Exception
     * @return Mage_Customer_Model_Customer
     */
    protected function loadCustomerById($id)
    {
        /* @var $customer Mage_Customer_Model_Customer */
        $customer = Mage::getModel('customer/customer')->load($id);
        if (!$customer->getId()) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }

        return $customer;
    }

    /**
     * Removes sensitive data from retrieval
     *
     * @todo-sg: should use the native filter in Shopgate_Cloudapi_Model_Api2_Resource::dispatch
     *
     * @param array $data
     *
     * @return array
     */
    protected function filterOutData(array $data)
    {
        $excludeKeys = array('password_hash', 'password', 'store');

        return array_diff_key($data, array_flip($excludeKeys));
    }

}
