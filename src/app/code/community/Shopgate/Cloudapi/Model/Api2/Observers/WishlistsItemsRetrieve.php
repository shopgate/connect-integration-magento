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

class Shopgate_Cloudapi_Model_Api2_Observers_WishlistsItemsRetrieve
{
    /**
     * Remember, wishlist data is returned by reference here.
     *
     * @param Varien_Event_Observer $observer
     */
    public function execute(Varien_Event_Observer $observer)
    {
        /** @var Mage_Core_Model_Store $store */
        $store = $observer->getData('store');
        if (Mage::getStoreConfigFlag(Shopgate_Cloudapi_Helper_Data::PATH_OBSERVERS_WISHLISTS_ITEMS_RETRIEVE, $store)) {
            return;
        }

        /** @var Mage_Wishlist_Model_Resource_Item_Collection $collection */
        $collection = $observer->getData('collection');
        /** @var Varien_Object $output */
        $items = array();
        /** @var Mage_Wishlist_Model_Item $item */
        foreach ($collection as $item) {
            $this->appendChildId($item);
            $data               = $item->getData();
            $data['buyRequest'] = $item->getBuyRequest()->getData();
            $items[]            = $data;
        }

        $output = $observer->getData('output');
        $output->setData('items', $items);
    }

    /**
     * @param Mage_Wishlist_Model_Item $item
     */
    private function appendChildId($item)
    {
        $buyRequestItem = new Varien_Object($item->getBuyRequest()->getData());
        $superAttribute = $buyRequestItem->getSuperAttribute();
        if (is_array($superAttribute) && count($superAttribute)) {
            $parentProduct = Mage::getModel('catalog/product')->load($item->getProductId());
            $childProduct  = Mage::getModel('catalog/product_type_configurable')->getProductByAttributes(
                $superAttribute,
                $parentProduct
            );
            $item->setChildId($childProduct->getId());
        }
    }
}
