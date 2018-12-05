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

class Shopgate_Cloudapi_Helper_Api2_Wishlists extends Mage_Core_Helper_Abstract
{
    /**
     * Enterprise setting to allow wishlists to be shared
     */
    const VISIBILITY_PRIVATE = 0;
    const VISIBILITY_PUBLIC  = 1;

    /**
     * Mapping for user readable text to Mage table code
     *
     * @var array
     */
    private $visibility = array(
        'private' => self::VISIBILITY_PRIVATE,
        'public'  => self::VISIBILITY_PUBLIC
    );

    /**
     * @param string|null $visibility - 'private' or 'public'
     *
     * @return int
     */
    public function translateVisibility($visibility)
    {
        return isset($this->visibility[$visibility]) ? $this->visibility[$visibility] : self::VISIBILITY_PRIVATE;
    }

    /**
     * Check if we are using EE and multiple wishlists are enabled
     *
     * @return bool
     */
    public function isEnterpriseMultilist()
    {
        $helper = $this->getEnterpriseHelper();

        return $helper && $helper->isMultipleEnabled();
    }

    /**
     * @param Mage_Wishlist_Model_Wishlist $wishlist
     * @param string                       $name       - wishlist name, doesn't have to be unique
     * @param int                          $visibility - 1 for public, 0 for private
     *
     * @return string
     * @throws Mage_Core_Exception
     */
    public function createEnterpriseWishlist(Mage_Wishlist_Model_Wishlist $wishlist, $name, $visibility)
    {
        $wishlistCollection = $wishlist->getCollection()->filterByCustomerId($wishlist->getCustomerId());
        if ($this->getEnterpriseHelper()->isWishlistLimitReached($wishlistCollection)) {
            $limit = $this->getEnterpriseHelper()->getWishlistLimit();
            Mage::throwException(
                Mage::helper('enterprise_wishlist')->__('Only %d wishlists can be created.', $limit)
            );
        }
        $wishlist
            ->generateSharingCode()
            ->setData('name', $name)
            ->setData('visibility', $visibility);

        return $wishlist->save();
    }

    /**
     * @return Enterprise_Wishlist_Helper_Data|false
     */
    public function getEnterpriseHelper()
    {
        return class_exists('Enterprise_Wishlist_Helper_Data') ? Mage::helper('enterprise_wishlist') : false;
    }
}
