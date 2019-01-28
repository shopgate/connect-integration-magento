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

class Shopgate_Cloudapi_Model_Api2_Observers_WishlistsDelete
{
    /**
     * @param Varien_Event_Observer $observer
     *
     * @throws Mage_Core_Model_Store_Exception
     * @throws Exception
     */
    public function execute(Varien_Event_Observer $observer)
    {
        /** @var Mage_Core_Model_Store $store */
        $store = $observer->getData('store');
        if (Mage::getStoreConfigFlag(Shopgate_Cloudapi_Helper_Data::PATH_OBSERVERS_WISHLISTS_DELETE, $store)) {
            return;
        }

        /** @var Mage_Wishlist_Model_Wishlist $wishlist */
        $wishlist = $observer->getData('wishlist');
        $wishlist->delete();
    }
}
