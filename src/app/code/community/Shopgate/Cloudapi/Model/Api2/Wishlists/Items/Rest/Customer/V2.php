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

class Shopgate_Cloudapi_Model_Api2_Wishlists_Items_Rest_Customer_V2
    extends Shopgate_Cloudapi_Model_Api2_Wishlists_Items_Rest
{
    /**
     * Add a wishlist product to the list
     *
     * @inheritdoc
     * @throws Mage_Api2_Exception
     * @throws Exception
     */
    public function _create(array $filteredData)
    {
        $wishlist = $this->getWishlist();
        $this->validateProductData($filteredData);
        try {
            Mage::dispatchEvent(
                'shopgate_cloud_api2_wishlists_items_create',
                array(
                    'input'    => $filteredData,
                    'wishlist' => $wishlist,
                    'store'    => $this->_getStore()
                )
            );
        } catch (Exception $e) {
            $this->_critical($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }

        /** @var Mage_Wishlist_Model_Item $wishlistItem */
        $wishlistItem = $wishlist->getData('last_added_item');
        $this->validateWishListItem($wishlistItem);

        return array('wishlistItemId' => $wishlistItem->getId());
    }

    /**
     * Makes sure that we have a valid product
     * before making the add product call
     *
     * @param array $data
     *
     * @throws Mage_Api2_Exception
     */
    private function validateProductData($data)
    {
        $helper = Mage::helper('wishlist');
        if (isset($data['product_id'])) {
            $productId      = $data['product_id'];
            $identifierType = 'id';
        } elseif (isset($data['sku'])) {
            $productId      = $data['sku'];
            $identifierType = 'sku';
        } else {
            $this->_critical($helper->__('Cannot specify product.'), Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        }
        /** @noinspection PhpUndefinedVariableInspection */
        $product = Mage::helper('catalog/product')
                       ->getProduct($productId, $this->_getStore()->getId(), $identifierType);
        if (!$product->getId() || !$product->isVisibleInCatalog()) {
            $error = $helper->__('Unable to add the following product(s) to shopping cart: %s.', $productId);
            $this->_critical($error, Mage_Api2_Model_Server::HTTP_NOT_FOUND);
        }
    }
}
