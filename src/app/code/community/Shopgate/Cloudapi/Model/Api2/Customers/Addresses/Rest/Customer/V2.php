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

class Shopgate_Cloudapi_Model_Api2_Customers_Addresses_Rest_Customer_V2
    extends Shopgate_Cloudapi_Model_Api2_Customers_Addresses_Rest
{
    /**
     * Parameter accepted by the endpoint to filter address collection by id
     */
    const PARAMETER_ADDRESS_FILTER = 'ids';

    /**
     * Load customer address by id
     *
     * @param int $id
     *
     * @throws Mage_Api2_Exception
     * @throws Exception
     * @return Mage_Customer_Model_Address
     */
    protected function _loadCustomerAddressById($id)
    {
        /* @var $customerAddress Mage_Customer_Model_Address */
        $customerAddress = parent::_loadCustomerAddressById($id);
        // check owner
        if ($this->getApiUser()->getUserId() !== $customerAddress->getCustomerId()) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }

        return $customerAddress;
    }

    /**
     * Load customer by id
     *
     * @param int $id
     *
     * @throws Mage_Api2_Exception*
     * @throws Exception
     * @return Mage_Customer_Model_Customer
     */
    protected function _loadCustomerById($id)
    {
        /* @var $customer Mage_Customer_Model_Customer */
        $customer = parent::_loadCustomerById($id);
        // check customer account owner
        if ($this->getApiUser()->getUserId() !== $customer->getId()) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }

        return $customer;
    }

    /**
     * @param array $data
     *
     * @throws Mage_Api2_Exception
     */
    protected function _multiDelete(array $data)
    {
        if (isset($data[self::PARAMETER_ADDRESS_FILTER])) {
            foreach (explode(',', $data[self::PARAMETER_ADDRESS_FILTER]) as $addressId) {
                $this->_deleteAddressById($addressId);
            }
        }
    }
}
