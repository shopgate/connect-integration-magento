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

class Shopgate_Cloudapi_Model_Api2_Observers_WishlistStoreHandle
{
    /**
     * This is needed due to some Mage EE bug in 1.13 version.
     * We actually create recursion here, but hopefully
     * nothing goes wrong and we manage to force set the
     * current store ID without it being re-written by
     * somebody after us.
     *
     * @param Varien_Event_Observer $observer
     */
    public function execute(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('shopgate_cloudapi/request')->isShopgateApi()) {
            return;
        }
        $version = Mage::getVersion();
        if (version_compare($version, '1.13.0.0', '<')
            || version_compare($version, '1.14.0.0', '>=')
        ) {
            return;
        }

        try {
            $storeId = Mage::app()->getStore()->getId();
        } catch (Mage_Core_Model_Store_Exception $exception) {
            Mage::logException($exception);

            return;
        }

        /** @var Mage_Catalog_Model_Resource_Product_Collection $collection */
        $collection = $observer->getEvent()->getData('collection');
        $filters    = $collection->getLimitationFilters();
        /** @noinspection NotOptimalIfConditionsInspection */
        if (isset($filters['store_id'], $filters['website_id']) && $filters['store_id'] !== $storeId) {
            $collection->addStoreFilter($storeId);
        }
    }
}
