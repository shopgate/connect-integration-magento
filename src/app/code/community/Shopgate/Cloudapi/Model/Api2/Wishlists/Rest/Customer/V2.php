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

class Shopgate_Cloudapi_Model_Api2_Wishlists_Rest_Customer_V2 extends Shopgate_Cloudapi_Model_Api2_Wishlists_Rest
{
    /** @noinspection PhpHierarchyChecksInspection */
    /**
     * Enterprise or 3rd party point, creates a new
     * wishlist if multiple wishlists are possible.
     * Else returns the ID of the default wishlist.
     *
     * @inheritdoc
     * @throws Mage_Api2_Exception
     * @throws Exception
     */
    public function _create(array $filteredData)
    {
        $wishlist = Mage::getModel('wishlist/wishlist')->setCustomerId($this->getApiUser()->getUserId());
        try {
            Mage::dispatchEvent(
                'shopgate_cloud_api2_wishlists_create',
                array('input' => $filteredData, 'wishlist' => $wishlist, 'store' => $this->_getStore())
            );
        } catch (Mage_Core_Exception $e) {
            $this->_critical($e->getMessage(), Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        } catch (Exception $e) {
            $this->_critical($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }

        if (!$wishlist->getId()) {
            $this->_critical(
                Mage::helper('wishlist')->__('Wishlist could not be created.'),
                Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR
            );
        }

        /** @noinspection PhpUndefinedVariableInspection */
        return array('wishlistId' => $wishlist->getId());
    }
}
