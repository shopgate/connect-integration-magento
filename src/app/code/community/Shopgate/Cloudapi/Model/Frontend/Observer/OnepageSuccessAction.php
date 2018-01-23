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

class Shopgate_Cloudapi_Model_Frontend_Observer_OnepageSuccessAction
{
    const PREVENT_OBSERVER_CHECKOUT_SUCCESS_KEY = 'prevent_observer_checkout_success';

    /**
     * Checks if the order is received from Shopgate API call.
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function execute(Varien_Event_Observer $observer)
    {
        $orderIds = $observer->getEvent()->getData('order_ids');

        if (!isset($orderIds[0]) || !Mage::helper('shopgate_cloudapi/request')->isShopgateRequest() || Mage::registry(
                self::PREVENT_OBSERVER_CHECKOUT_SUCCESS_KEY
            )
        ) {
            return $this;
        }

        $newOrderId = $orderIds[0];
        $layout     = Mage::app()->getLayout();

        $head = $layout->getBlock('head');
        $head->addJs('shopgate/sgEvents.js');

        /** @var Shopgate_Cloudapi_Block_Checkout_Onepage_Success $successBlock */
        $successBlock = $layout->createBlock('shopgate_cloudapi/checkout_onepage_success');
        $successBlock->setTemplate('shopgate/cloudapi/header/checkout/onepage/success.phtml');
        $successBlock->setOrderId($newOrderId);

        $head->append($successBlock);

        Mage::register(self::PREVENT_OBSERVER_CHECKOUT_SUCCESS_KEY, true);
    }
}
