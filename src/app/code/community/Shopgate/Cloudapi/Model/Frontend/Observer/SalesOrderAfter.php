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

class Shopgate_Cloudapi_Model_Frontend_Observer_SalesOrderAfter
{
    /**
     * Shopgate store code
     */
    const SHOPGATE_STORE_CODE = 'shopgate';

    /**
     * Checks if the order is received from Shopgate API call.
     *
     * @todo-sg: send the order to the pipeline for tracking
     *
     * @param Varien_Event_Observer $observer
     *
     * @throws Mage_Core_Exception
     */
    public function execute(Varien_Event_Observer $observer)
    {
        if (Mage::helper('shopgate_cloudapi/request')->isShopgateRequest()
            && !Mage::registry('prevent_observer')
        ) {
            /** @var Mage_Sales_Model_Order $order */
            $order           = $observer->getEvent()->getData('order');
            $shopgateStoreId = $this->getShopgateStoreId();
            if ($shopgateStoreId !== false) {
                $order->setStoreId($shopgateStoreId);
            }
            /** @var Shopgate_Cloudapi_Model_Order_Source $orderSourceModel */
            $orderSourceModel = Mage::getModel('shopgate_cloudapi/order_source');
            $orderSourceModel->addForWebCheckout($order->getId());
            Mage::register('prevent_observer', true);
        }
    }
    
    /**
     * @return int|false
     */
    protected function getShopgateStoreId()
    {
        /** @var Mage_Core_Model_Store $store */
        try {
            $store = Mage::app()->getStore(self::SHOPGATE_STORE_CODE);
        } catch (Mage_Core_Model_Store_Exception $exception) {
            return false;
        }
        
        if ($store->getId()) {
            return $store->getId();
        }
        
        return false;
    }
}
