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
    /** @noinspection PhpHierarchyChecksInspection */
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
        $this->validateIncomingProduct($filteredData);
        $wishlistItem = $this->addItemToWishlist($filteredData, $wishlist);
        $this->validateWishListItem($wishlistItem);

        return array('wishlistItemId' => $wishlistItem->getId());
    }

    /** @noinspection PhpHierarchyChecksInspection */
    /**
     * Adds multiple items at the same time and skips
     * the ones with issues that we can catch
     *
     * @inheritdoc
     * @throws Mage_Api2_Exception
     */
    public function _multiCreate(array $filteredData)
    {
        $exception = count($filteredData) === 1;
        $idList    = array();
        $wishlist  = $this->getWishlist();
        foreach ($filteredData as $itemData) {
            if (!$this->validateIncomingProduct($itemData, $exception)) {
                continue;
            }

            $item = $this->addItemToWishlist($itemData, $wishlist);
            if ($this->validateWishListItem($item, $exception)) {
                $idList[$this->getId($itemData)] = $item->getId();
            }
        }

        if (empty($idList)) {
            $this->_critical(
                Mage::helper('wishlist')->__('Cannot specify product.'),
                Mage_Api2_Model_Server::HTTP_BAD_REQUEST
            );
        }

        return array('wishlistItemIds' => $idList);
    }

    /** @noinspection PhpHierarchyChecksInspection */
    /**
     * @inheritdoc
     * @throws Mage_Api2_Exception
     */
    public function _retrieveCollection()
    {
        $collection = $this->getWishlist()->getItemCollection();
        $output     = new Varien_Object();
        try {
            Mage::dispatchEvent(
                'shopgate_cloud_api2_wishlists_items_retrieve',
                array(
                    'output'     => $output,
                    'collection' => $collection,
                    'store'      => $this->_getStore()
                )
            );
        } catch (Exception $e) {
            $this->_critical($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }

        return $output->getData('items');
    }

    /** @noinspection PhpHierarchyChecksInspection */
    /**
     * Remove wishlist products from the list
     *
     * @inheritdoc
     * @throws Mage_Api2_Exception
     * @throws Exception
     */
    public function _multiDelete(array $filteredData)
    {
        $wishlist        = $this->getWishlist();
        $wishlistItemIds = $this->getWishlistItemIds($wishlist);
        try {
            Mage::dispatchEvent(
                'shopgate_cloud_api2_wishlists_items_remove',
                array(
                    'wishlist'        => $wishlist,
                    'wishlistItemIds' => $wishlistItemIds,
                    'store'           => $this->_getStore()
                )
            );
        } catch (Exception $e) {
            $this->_critical($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * @param array                        $data - single product data
     * @param Mage_Wishlist_Model_Wishlist $wishlist
     *
     * @return Mage_Wishlist_Model_Item
     * @throws Mage_Api2_Exception
     */
    private function addItemToWishlist(array $data, Mage_Wishlist_Model_Wishlist $wishlist)
    {
        try {
            Mage::dispatchEvent(
                'shopgate_cloud_api2_wishlists_items_create',
                array(
                    'input'    => $data,
                    'wishlist' => $wishlist,
                    'store'    => $this->_getStore()
                )
            );
        } catch (Exception $e) {
            $this->_critical($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }

        return $wishlist->getData('last_added_item');
    }

    /**
     * Makes sure that we have a valid product
     * before making the add product call
     *
     * @param array $data
     * @param bool  $exception - to throw an exception or not
     *
     * @return bool
     * @throws Mage_Api2_Exception
     */
    private function validateIncomingProduct(array $data, $exception = true)
    {
        $id     = $this->getId($data);
        $helper = Mage::helper('wishlist');
        if (empty($id)) {
            if ($exception) {
                $this->_critical($helper->__('Cannot specify product.'), Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
            }

            return false;
        }
        /** @noinspection PhpUndefinedVariableInspection */
        $product = Mage::helper('catalog/product')
                       ->getProduct($id, $this->_getStore()->getId(), is_int($id) ? 'id' : 'sku');
        if (!$product->getId() || !$product->isVisibleInCatalog()) {
            if ($exception) {
                $error = $helper->__('An error occurred while adding item to wishlist: %s', $id);
                $this->_critical($error, Mage_Api2_Model_Server::HTTP_NOT_FOUND);
            }

            return false;
        }

        return true;
    }

    /**
     * Returns the ID of the incoming item request
     *
     * @param array $data
     *
     * @return int | string | false
     */
    private function getId(array $data)
    {
        if (isset($data['product_id']) || isset($data['sku'])) {
            return isset($data['product_id']) ? (int) $data['product_id'] : $data['sku'];
        }

        return false;
    }
}
