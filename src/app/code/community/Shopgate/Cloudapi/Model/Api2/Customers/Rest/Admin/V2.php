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

class Shopgate_Cloudapi_Model_Api2_Customers_Rest_Admin_V2 extends Shopgate_Cloudapi_Model_Api2_Customers_Rest
{
    /** @noinspection PhpHierarchyChecksInspection */
    /**
     * Prevent guest from calling ME endpoint, adjust accordingly
     *
     * @throws Mage_Api2_Exception
     */
    protected function _retrieve()
    {
        $this->_critical(self::RESOURCE_METHOD_NOT_ALLOWED, Mage_Api2_Model_Server::HTTP_FORBIDDEN);
    }

    /** @noinspection PhpHierarchyChecksInspection */
    /**
     * Prevent guest from calling customer update endpoint, adjust accordingly
     *
     * @param array $filteredData
     *
     * @throws Mage_Api2_Exception
     */
    protected function _update(array $filteredData)
    {
        $this->_critical(self::RESOURCE_METHOD_NOT_ALLOWED, Mage_Api2_Model_Server::HTTP_FORBIDDEN);
    }

    /** @noinspection PhpHierarchyChecksInspection */
    /**
     * @param array $filteredData
     *
     * @return array
     * @throws Mage_Core_Exception
     */
    protected function _create($filteredData)
    {
        /** @var Mage_Customer_Model_Customer $customer */
        $customer = Mage::getModel("customer/customer");

        $newCustomerData = new Varien_Object();
        $newCustomerData->setData($filteredData);
        $newCustomerData->setStore($this->getStore($newCustomerData));
        $newCustomerData->setWebsiteId($this->getWebsiteId($newCustomerData));

        $customer->setData($newCustomerData->getData());

        try {
            $customer->save();
        } catch (Exception $e) {
            $this->_critical($e->getMessage());
        }

        return $this->filterOutData($customer->getData());
    }

    /**
     * @param Varien_Object $requestData
     *
     * @return Mage_Core_Model_Store
     * @throws Mage_Api2_Exception
     */
    protected function getStore($requestData)
    {
        if ($requestData->hasData('store_id')) {
            $store = Mage::getModel('core/store')->load($requestData->getStoreId());
            if (!$store->getid()) {
                $this->_critical(self::RESOURCE_REQUEST_DATA_INVALID, Mage_Api2_Model_Server::HTTP_NOT_FOUND);
            }

            return Mage::getModel('core/store')->load($requestData->getStoreId());
        }

        return $this->_getStore();
    }

    /**
     * @param array $requestData
     *
     * @return integer
     * @throws Mage_Api2_Exception
     */
    protected function getWebsiteId($requestData)
    {
        if ($requestData->hasData('website_id')) {
            if ($requestData->getStore()->getWebsiteId() != $requestData->getWebsiteId()) {
                $this->_critical(self::RESOURCE_REQUEST_DATA_INVALID, Mage_Api2_Model_Server::HTTP_NOT_FOUND);
            }

            return $requestData->getWebsiteId();
        }

        return $requestData->getStore()->getWebsiteId();
    }
}
