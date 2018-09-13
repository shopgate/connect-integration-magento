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
class Shopgate_Cloudapi_Model_Api2_Customers_Rest_Customer_V2 extends Shopgate_Cloudapi_Model_Api2_Customers_Rest
{
    /** @noinspection PhpHierarchyChecksInspection */
    /**
     * Retrieve customer data
     *
     * @throws Exception
     */
    protected function _retrieve()
    {
        $userId                         = $this->getApiUser()->getUserId();
        $customerData                   = Mage::getModel('customer/customer_api')->info($userId);
        $groupItem                      = Mage::getModel('customer/group')->load($customerData['group_id']);
        $customerData['customer_group'] = $groupItem->getData();

        return $this->filterOutData($customerData);
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
    private function filterOutData(array $data)
    {
        $excludeKeys = array('password_hash');

        return array_diff_key($data, array_flip($excludeKeys));
    }

    /** @noinspection PhpHierarchyChecksInspection */
    /**
     * Update customer data
     *
     * @param array $filteredData
     *
     * @throws Exception
     */
    protected function _update(array $filteredData)
    {
        $validator    = $this->_getValidator();
        $filteredData = $validator->filter($filteredData);
        if (!$validator->isValidData($filteredData, true)) {
            $this->_render($this->setDetailedErrors($validator)->sendInvalidationResponse());

            return;
        }

        try {
            Mage::getModel('customer/customer_api')->update($this->getApiUser()->getUserId(), $filteredData);
        } catch (Mage_Core_Exception $e) {
            $this->_error($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        } catch (Exception $e) {
            $this->_critical(self::RESOURCE_INTERNAL_ERROR);
        }
    }
}
