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

class Shopgate_Cloudapi_Model_Api2_Wishlists_Items_Rest extends Shopgate_Cloudapi_Model_Api2_Wishlists_Rest
{
    /**
     * @param Mage_Wishlist_Model_Item | string $wishlistItem
     * @param bool                              $exception - whether to throw an exception in this function
     *
     * @return bool
     * @throws Mage_Api2_Exception
     */
    protected function validateWishListItem($wishlistItem, $exception = true)
    {
        if (is_string($wishlistItem)) {
            if ($exception) {
                $this->_critical($wishlistItem, Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
            }

            return false;
        }
        if ($wishlistItem->getData('has_error')) {
            if ($exception) {
                $this->_critical($wishlistItem->getData('message'), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
            }

            return false;
        }
        if (!$wishlistItem->getId() || $wishlistItem->isDeleted()) {
            if ($exception) {
                $error = Mage::helper('wishlist')->__('An error occurred while adding item to wishlist.');
                $this->_critical($error, Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
            }

            return false;
        }

        return true;
    }

    /**
     * Sanitizer for incoming data
     *
     * @param Mage_Wishlist_Model_Wishlist $wishlist
     *
     * @throws Exception
     * @return array
     */
    protected function getWishlistItemIds($wishlist)
    {
        $wishlistItemIds = $this->getRequest()->getParam('wishlistItemIds');

        if (is_null($wishlistItemIds)) {
            return $wishlist->getItemCollection()->getAllIds();
        }

        if ($wishlistItemIds === '') {
            $this->_critical(self::RESOURCE_REQUEST_DATA_INVALID);
        }

        return explode(',', $wishlistItemIds);
    }
}
