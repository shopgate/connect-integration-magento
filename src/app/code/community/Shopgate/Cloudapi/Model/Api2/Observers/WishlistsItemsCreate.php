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

class Shopgate_Cloudapi_Model_Api2_Observers_WishlistsItemsCreate
{
    /**
     * Remember, wishlist item data is returned by reference here
     *
     * @param Varien_Event_Observer $observer
     *
     * @throws Mage_Core_Model_Store_Exception
     */
    public function execute(Varien_Event_Observer $observer)
    {
        /** @var Mage_Core_Model_Store $store */
        $store = $observer->getData('store');
        if (Mage::getStoreConfigFlag(Shopgate_Cloudapi_Helper_Data::PATH_OBSERVERS_WISHLISTS_ITEMS_CREATE, $store)) {
            return;
        }

        /** @var Mage_Wishlist_Model_Wishlist $wishlist */
        /** @var Mage_Catalog_Model_Product $product */
        $wishlist = $observer->getData('wishlist');
        $input    = $observer->getData('input');
        $product  = $this->getProduct($input, $store->getId());

        /**
         * This is needed as when the item collection is loaded
         * deep down in the logic, the items within it are flagged
         * for deletion. So on item save it will delete the last item.
         *
         * @see Mage_Wishlist_Model_Resource_Item_Collection::_assignProducts()
         */
        $adminStore = Mage::app()->getStore()->getCode();
        Mage::app()->setCurrentStore($store->getCode());
        $result = $wishlist->addNewItem($product, $input);
        Mage::app()->setCurrentStore($adminStore);

        $wishlist->setData('last_added_item', $result);
    }

    /**
     * Assumes that a previous validation
     * will determine if either ID or SKU
     * exist
     *
     * @param array  $data
     * @param string $storeId
     *
     * @return Mage_Catalog_Model_Product
     */
    private function getProduct(array $data, $storeId)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $product = isset($data['product_id'])
            ? Mage::helper('catalog/product')->getProduct($data['product_id'], (int) $storeId, 'id')
            : Mage::helper('catalog/product')->getProduct($data['sku'], (int) $storeId, 'sku');

        return $product;
    }
}
