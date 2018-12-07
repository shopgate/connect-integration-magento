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
     * @param array $data
     *
     * @return array
     * @throws Mage_Api2_Exception
     * @throws Zend_Controller_Response_Exception
     * @throws Mage_Core_Exception
     */
    protected function _create($data)
    {
        $validator    = $this->getValidator();
        $filteredData = $validator->filter($data);
        if (!$validator->isValidData($filteredData)) {
            return $this->setDetailedErrors($validator)->sendInvalidationResponse();
        }

        /** @var $customer Mage_Customer_Model_Customer */
        $customer = Mage::getModel('customer/customer');
        $customer->setData($filteredData);

        try {
            $customer->save();
        } catch (Mage_Core_Exception $e) {
            $this->_critical($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        } catch (Exception $e) {
            $this->_critical(self::RESOURCE_INTERNAL_ERROR);
        }

        return array('customerId' => $customer->getId());
    }

    /**
     * @return Shopgate_Cloudapi_Model_Api2_Customers_Validator
     */
    public function getValidator()
    {
        return Mage::getModel('shopgate_cloudapi/api2_customers_validator', array('resource' => $this));
    }
}
