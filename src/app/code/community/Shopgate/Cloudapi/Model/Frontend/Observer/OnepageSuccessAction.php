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
     * Checks if the order is received from Shopgate API call.
     *
     * @param Varien_Event_Observer $observer
     */
    public function execute(Varien_Event_Observer $observer)
    {
        $session = Mage::getSingleton('checkout/session');

        if ($session->getData(Shopgate_Cloudapi_Helper_Frontend_Checkout::SESSION_IS_SHOPGATE_CHECKOUT)) {
            $orderIds = $observer->getEvent()->getData('order_ids');
            if (isset($orderIds[0])) {
                $newOrderId = $orderIds[0];
                $layout     = Mage::app()->getLayout();
                /** @var Shopgate_Cloudapi_Block_PipelineRequest $pipelineRequestBlock */
                $pipelineRequestBlock = $layout->createBlock('shopgate_cloudapi/pipelineRequest');
                $pipelineRequestBlock->addMethod(
                    array(
                        'serial' => Shopgate_Cloudapi_Block_PipelineRequest::PIPELINE_REQUEST_SERIAL, //@todo evaluate the right serial
                        'name'   => Shopgate_Cloudapi_Block_PipelineRequest::PIPELINE_REQUEST_CREATE_NEW_CART_FOR_CUSTOMER,
                        'input'  => array(
                            'orderId' => $newOrderId
                        )
                    )
                );
                $pipelineRequestBlock->setTemplate('shopgate/cloudapi/pipelineRequest.phtml');
                $layout->getBlock('head')->addJs('shopgate/pipelineRequest.js');
                $layout->getBlock('head')->append($pipelineRequestBlock);

                /** @var Shopgate_Cloudapi_Block_AnalyticsLogPurchase  $analyticsLogPurchaseBlock */
                $analyticsLogPurchaseBlock = $layout->createBlock('shopgate_cloudapi/analyticsLogPurchase');
                $analyticsLogPurchaseBlock->setOrderId($newOrderId);
                $analyticsLogPurchaseBlock->setTemplate('shopgate/cloudapi/analyticsLogPurchase.phtml');
                $layout->getBlock('head')->addJs('shopgate/analyticsLogPurchase.js');
                $layout->getBlock('head')->append($analyticsLogPurchaseBlock);
            }
            $session->unsetData(Shopgate_Cloudapi_Helper_Frontend_Checkout::SESSION_IS_SHOPGATE_CHECKOUT);
        }
    }
}
