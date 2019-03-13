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
 * Abstract API2 class for order item
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author     Magento Core Team <core@magentocommerce.com>
 */
abstract class Shopgate_Cloudapi_Model_Api2_Orders_Rest extends Shopgate_Cloudapi_Model_Api2_Orders
{
    /**
     * Used to create the join
     */
    const TABLE_ALIAS = 'shopgate_orders';
    /**
     * Field name to prepend
     */
    const FIELD_START = 'shopgate_order_';
    /**
     * Whitelisting actions that can be filtered by
     *
     * @var array
     */
    protected $operationWhitelist = array('lt', 'gt', 'eq', 'gteq', 'lteq', 'neq', 'like', 'notnull');

    /**
     * Retrieve information about specified order item
     *
     * @return array
     * @throws Exception
     * @throws Mage_Api2_Exception
     */
    protected function _retrieve()
    {
        $orderId    = $this->getRequest()->getParam('id');
        $collection = $this->_getCollectionForSingleRetrieve($orderId);

        if ($this->_isPaymentMethodAllowed()) {
            $this->_addPaymentMethodInfo($collection);
        }
        if ($this->_isGiftMessageAllowed()) {
            $this->_addGiftMessageInfo($collection);
        }
        $this->_addTaxInfo($collection);

        $order = $collection->getItemById($orderId);

        if (!$order) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        $orderData = $order->getData();
        $addresses = $this->_getAddresses(array($orderId));
        $items     = $this->_getItems(array($orderId));
        $comments  = $this->_getComments(array($orderId));

        if ($addresses) {
            $orderData['addresses'] = $addresses[$orderId];
        }
        if ($items) {
            $orderData['order_items'] = $items[$orderId];
        }
        if ($comments) {
            $orderData['order_comments'] = $comments[$orderId];
        }

        return $orderData;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    protected function _getCollectionForRetrieve()
    {
        $collection = parent::_getCollectionForRetrieve();
        $this->joinShopgateOrderSourcesTable($collection);
        $this->applyShopgateFilters($collection);

        return $collection;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    protected function _getCollectionForSingleRetrieve($orderId)
    {
        $collection = parent::_getCollectionForSingleRetrieve($orderId);
        $this->joinShopgateOrderSourcesTable($collection);
        $this->applyShopgateFilters($collection);

        return $collection;
    }

    /**
     * Flatten orders instead of array('order_id' => array(ORDER_DATA))
     *
     * @return array array('entity_id' => '...', ...)
     * @throws Exception
     */
    protected function _retrieveCollection()
    {
        return array_values(parent::_retrieveCollection());
    }

    /**
     * This will need Magento ACL Attribute -> Order -> Comments
     *
     * @inheritDoc
     */
    protected function _getComments(array $orderIds)
    {
        $this->setVersion('1');

        return parent::_getComments($orderIds);
    }

    /**
     * This will need Magento ACL Attribute -> Order -> Addresses
     *
     * @inheritDoc
     */
    protected function _getAddresses(array $orderIds)
    {
        $this->setVersion('1');

        return parent::_getAddresses($orderIds);
    }

    /**
     * This will need Magento ACL Attribute -> Order -> Items
     *
     * @inheritDoc
     */
    protected function _getItems(array $orderIds)
    {
        $this->setVersion('1');

        return parent::_getItems($orderIds);
    }

    /**
     * Left join shopgate_order_sources table and add appropriate column names for return
     *
     * @param Mage_Sales_Model_Resource_Order_Collection $collection
     */
    private function joinShopgateOrderSourcesTable(Mage_Sales_Model_Resource_Order_Collection $collection)
    {
        $table   = Mage::getResourceModel('shopgate_cloudapi/order_source')->getMainTable();
        $alias   = self::TABLE_ALIAS;
        $source  = Shopgate_Cloudapi_Model_Resource_Order_Source::COLUMN_SOURCE;
        $agent   = Shopgate_Cloudapi_Model_Resource_Order_Source::COLUMN_AGENT;
        $orderId = Shopgate_Cloudapi_Model_Resource_Order_Source::COLUMN_ORDER_ID;
        $collection->getSelect()->joinLeft(
            array($alias => $table),
            "main_table.entity_id = {$alias}.{$orderId}",
            array(
                self::FIELD_START . $source => "{$alias}.{$source}",
                self::FIELD_START . $agent  => "{$alias}.{$agent}"
            )
        );
        $collection->addFilterToMap($source, "{$alias}.{$source}")
                   ->addFilterToMap($agent, "{$alias}.{$agent}");
    }

    /**
     * Applies Shopgate order filters
     * Please note a SQL hard error when filtering by `page` and `shopgate_order_` fields.
     *
     * @param Mage_Sales_Model_Resource_Order_Collection $collection
     *
     * @throws Exception
     */
    private function applyShopgateFilters(Mage_Sales_Model_Resource_Order_Collection $collection)
    {
        $filters = $this->getShopgateFiltrationParams();

        foreach ($filters as $filter => $conditions) {
            foreach ($conditions as $field => $value) {
                $translated = $this->getConditions($value, $filter);
                $collection->addFieldToFilter(array($field), array($translated));
            }
        }
    }

    /**
     * Checks the params passed and filters out Magento
     * filter fields and conditions
     *
     * @return array array('lt' => array('created_at' => '2019-12-29'), 'eq' => array(...))
     * @throws Exception
     */
    private function getShopgateFiltrationParams()
    {
        $params          = $this->getRequest()->getParams();
        $whitelist       = $this->operationWhitelist;
        $validAttributes = $this->getAvailableAttributes($this->getUserType(), $this->getOperation());

        $searchParams = array();
        foreach ($params as $key => $value) {
            list($field, $lastPart) = $this->getShopgateParts($key);

            if (isset($validAttributes[$field]) && in_array($lastPart, $whitelist, true)) {
                $field                           = $this->removeShopgateFromField($field);
                $searchParams[$lastPart][$field] = $value;
            }
        }

        return $searchParams;
    }

    /**
     * Breaks down the incoming params for filtration
     *
     * @param string $key - e.g. shopgate_order_source_eq
     *
     * @return array e.g. array('shopgate_order_source', 'eq')
     */
    private function getShopgateParts($key)
    {
        $parts    = explode('_', $key);
        $lastPart = array_pop($parts);

        return array(implode('_', $parts), $lastPart);
    }

    /**
     * Truncates 'shopgate_order_' from string
     *
     * @param string $field - 'shopgate_order_source'
     *
     * @return string 'source'
     */
    private function removeShopgateFromField($field)
    {
        return str_replace(self::FIELD_START, '', $field);
    }

    /**
     * @param string $value  - 'halted,canceled' or 'webcheckout'
     * @param string $filter - 'eq'
     *
     * @return array array(array('eq' => 'halted'), array('eq' => 'canceled'))
     */
    private function getConditions($value, $filter)
    {
        return array_map(
            function ($value) use ($filter) {
                return array($filter => $value);
            },
            explode(',', $value)
        );
    }
}
