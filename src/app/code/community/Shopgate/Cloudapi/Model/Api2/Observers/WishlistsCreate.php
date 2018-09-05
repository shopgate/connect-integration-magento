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

class Shopgate_Cloudapi_Model_Api2_Observers_WishlistsCreate
{
    /**
     * Remember, wishlist data is returned by reference here
     *
     * @param Varien_Event_Observer $observer
     *
     * @throws Mage_Core_Exception
     */
    public function execute(Varien_Event_Observer $observer)
    {
        /** @var Mage_Core_Model_Store $store */
        $store = $observer->getData('store');
        if (Mage::getStoreConfigFlag(Shopgate_Cloudapi_Helper_Data::PATH_OBSERVERS_WISHLISTS_CREATE, $store)) {
            return;
        }

        /** @var Mage_Wishlist_Model_Wishlist $wishlist */
        $wishlist   = $observer->getData('wishlist');
        $input      = $observer->getData('input');
        $helper     = $this->getHelper();
        $visibility = $helper->translateVisibility(isset($input['visibility']) ? $input['visibility'] : null);
        $name       = isset($input['name']) ? $input['name'] : 'Default';

        if ($helper->isEnterpriseMultilist()) {
            $helper->createEnterpriseWishlist($wishlist, $name, $visibility);
        } elseif (!$wishlist->loadByCustomer($wishlist->getCustomerId(), false)->getId()) {
            $wishlist->loadByCustomer($wishlist->getCustomerId(), true);
        } else {
            Mage::throwException(Mage::helper('wishlist')->__('Wishlist could not be created.'));
        }
    }

    /**
     * @return Shopgate_Cloudapi_Helper_Api2_Wishlists
     */
    private function getHelper()
    {
        return Mage::helper('shopgate_cloudapi/api2_wishlists');
    }
}
