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
     * Remember, wishlist data is returned by reference here
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
            /** @var Mage_Catalog_Model_Product $product */
            $product            = Mage::getModel('catalog/product')->load($item->getProductId());
            $data               = $item->getData();
            $data['type']       = $product->getTypeId();
            $data['buyRequest'] = $item->getBuyRequest()->getData();
            $data['child_ids']  = $this->getChildIds($product, $item);
            $items[]            = $data;
        }

        $output = $observer->getData('output');
        $output->setData('items', $items);
    }

    /**
     * Returns the product child id(s).
     * For configurable we should get one child ID.
     * For bundle we get key (selection ID): value (child ID) pairs.
     *
     * @param Mage_Catalog_Model_Product $product
     * @param Mage_Wishlist_Model_Item   $item
     *
     * @return null | string[]
     */
    private function getChildIds(Mage_Catalog_Model_Product $product, Mage_Wishlist_Model_Item $item)
    {
        if ($product->getTypeId() === Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
            $superAttribute = $item->getBuyRequest()->getData('super_attribute');
            /** @var Mage_Catalog_Model_Product_Type_Configurable $productType */
            /** @var Mage_Catalog_Model_Resource_Product_Type_Configurable_Attribute_Collection $allAttributes */
            $productType     = $product->getTypeInstance();
            $childAttributes = $productType->getProductByAttributes($superAttribute, $product);

            return $childAttributes ? array($childAttributes->getId()) : null;
        }

        if ($product->getTypeId() === Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
            $options = $item->getBuyRequest()->getData('bundle_option');
            /** @var Mage_Bundle_Model_Product_Type $productType */
            $productType = $product->getTypeInstance();
            $list        = null;
            $selections  = !empty($options)
                ? $productType->getSelectionsByIds(array_values($options), $product)->getItems()
                : array();
            /** @var  Mage_Catalog_Model_Product $option */
            foreach ($selections as $key => $option) {
                $list[$key] = $option->getId();
            }

            return $list;
        }

        return null;
    }
}
