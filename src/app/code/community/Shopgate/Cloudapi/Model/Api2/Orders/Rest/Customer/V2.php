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

/**
 * API2 class for orders (customer)
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Shopgate_Cloudapi_Model_Api2_Orders_Rest_Customer_V2 extends Shopgate_Cloudapi_Model_Api2_Orders_Rest
{
    /**
     * Retrieve collection instance for orders
     *
     * @return Mage_Sales_Model_Resource_Collection_Abstract|Mage_Sales_Model_Resource_Order_Collection
     * @throws Exception
     */
    protected function _getCollectionForRetrieve()
    {
        return parent::_getCollectionForRetrieve()->addAttributeToFilter(
            'customer_id',
            array('eq' => $this->getApiUser()->getUserId())
        );
    }

    /**
     * Retrieve collection instance for single order
     *
     * @param int $orderId Order identifier
     *
     * @return Mage_Sales_Model_Resource_Collection_Abstract|Mage_Sales_Model_Resource_Order_Collection
     * @throws Exception
     */
    protected function _getCollectionForSingleRetrieve($orderId)
    {
        return parent::_getCollectionForSingleRetrieve($orderId)->addAttributeToFilter(
            'customer_id',
            array('eq' => $this->getApiUser()->getUserId())
        );
    }

    /**
     * Prepare and return order comments collection
     *
     * @param array $orderIds Orders' identifiers
     *
     * @return Mage_Sales_Model_Resource_Order_Status_History_Collection|Object
     */
    protected function _getCommentsCollection(array $orderIds)
    {
        return parent::_getCommentsCollection($orderIds)->addFieldToFilter('is_visible_on_front', 1);
    }
}
