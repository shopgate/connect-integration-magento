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

class Shopgate_Cloudapi_Model_Api2_Carts_Factory
{
    /** @var Shopgate_Cloudapi_Model_Api2_Carts_Items_List */
    protected $itemList;

    /**
     * @param array                 $requestData - the incoming request data
     * @param Mage_Core_Model_Store $store
     *
     * @return Shopgate_Cloudapi_Model_Api2_Carts_Items_List
     * @throws Mage_Api2_Exception
     */
    public function translateCartItems($requestData, $store)
    {
        foreach ($requestData as $requestItem) {
            $item = $this->getItem($requestItem, $store);
            $this->addToItemList($item);
        }

        return $this->getItemsList();
    }

    /**
     * @return Shopgate_Cloudapi_Model_Api2_Carts_Items_List
     */
    public function getItemsList()
    {
        if (null === $this->itemList) {
            $this->itemList = Mage::getModel('shopgate_cloudapi/api2_carts_items_list');
        }

        return $this->itemList;
    }

    /**
     * Retrieves the item to make add/update/delete actions on
     *
     * @param array                 $data - raw item row as defined by the add/update specification
     * @param Mage_Core_Model_Store $store
     *
     * @return Shopgate_Cloudapi_Model_Api2_Carts_Items_Item
     * @throws Mage_Api2_Exception
     */
    protected function getItem(array $data, Mage_Core_Model_Store $store)
    {
        $item = Mage::getModel('shopgate_cloudapi/api2_carts_items_item');
        if ($this->isItemUpdate($data)) {
            $cartItemId = array_shift($data);
            $item->setCartItemId($cartItemId);
        }

        $type = key($data);
        $info = current($data);

        $entry = $this->getItemEntry($type);
        $entry->setData($info)->setStore($store);
        $item->setEntry($entry);

        return $item;
    }

    /**
     * Retrieves a class that corresponds to this item type
     *
     * @param string $type - product, coupon or other type classes we have created
     *
     * @return Shopgate_Cloudapi_Model_Api2_Carts_Items_Type_Entry
     * @throws Mage_Api2_Exception
     */
    protected function getItemEntry($type)
    {
        $type  = lcfirst($type);
        $entry = Mage::getModel('shopgate_cloudapi/api2_carts_items_type_' . $type);

        if (!$entry instanceof Shopgate_Cloudapi_Model_Api2_Carts_Items_Type_Entry) {
            throw new Mage_Api2_Exception(
                sprintf('Invalid Item Type: %s', $type), Mage_Api2_Model_Server::HTTP_BAD_REQUEST
            );
        }

        return $entry;
    }

    /**
     * Is this an update call object. Updated objects looks like this:
     * [cartItemId => 1234, product => array(product_id => ...)]
     *
     * @param array $data
     *
     * @return bool
     */
    protected function isItemUpdate(array $data)
    {
        return count($data) === 2;
    }

    /**
     * Adds item to an internal array
     *
     * @param Shopgate_Cloudapi_Model_Api2_Carts_Items_Item $item
     *
     * @return $this
     */
    protected function addToItemList(Shopgate_Cloudapi_Model_Api2_Carts_Items_Item $item)
    {
        $this->getItemsList()->addItem($item);

        return $this;
    }
}
