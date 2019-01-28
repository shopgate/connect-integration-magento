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
    /**
     * Prevent observer checkout success
     */
    const PREVENT_OBSERVER_CHECKOUT_SUCCESS_KEY = 'prevent_observer_checkout_success';

    /**
     * Shopgate checkout onepage template path
     */
    const CHECKOUT_ONEPAGE_SUCCESS_TEMPLATE_PATH = 'shopgate/cloudapi/checkout/success/header.phtml';

    /**
     * Shopgate Events js path
     */
    const SHOPGATE_CONNECT_JS_PATH = 'shopgate/sgConnect.js';

    /**
     * Checks if the order is received from Shopgate App,
     * if so, we will need to log it via the JS script.
     *
     * @param Varien_Event_Observer $observer
     *
     * @throws Mage_Core_Exception
     */
    public function execute(Varien_Event_Observer $observer)
    {
        $orderIds = $observer->getEvent()->getData('order_ids');

        if ($this->shouldNotExecute($orderIds)) {
            return;
        }

        $newOrderId = $orderIds[0];
        $layout     = Mage::app()->getLayout();

        /** @var Mage_Page_Block_Html_Head $head */
        $head = $layout->getBlock('head');
        $head->addJs(self::SHOPGATE_CONNECT_JS_PATH);

        /** @var Shopgate_Cloudapi_Block_Checkout_Onepage_Success $successBlock */
        $successBlock = $layout->createBlock('shopgate_cloudapi/checkout_onepage_success');
        $successBlock->setTemplate(self::CHECKOUT_ONEPAGE_SUCCESS_TEMPLATE_PATH);
        $successBlock->setOrderId($newOrderId);

        $head->append($successBlock);

        Mage::register(self::PREVENT_OBSERVER_CHECKOUT_SUCCESS_KEY, true);
    }

    /**
     * Checks that we have an order available
     * That it is a Shopgate order
     * That it's not a second reload of this page
     *
     * @param array $orderIds
     *
     * @return bool
     */
    protected function shouldNotExecute($orderIds)
    {
        return !isset($orderIds[0])
            || !Mage::helper('shopgate_cloudapi/request')->isShopgateRequest()
            || Mage::registry(self::PREVENT_OBSERVER_CHECKOUT_SUCCESS_KEY);
    }
}
