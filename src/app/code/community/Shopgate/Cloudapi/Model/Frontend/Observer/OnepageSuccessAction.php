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
     * @return $this
     */
    public function execute(Varien_Event_Observer $observer)
    {
        $orderIds = $observer->getEvent()->getData('order_ids');

        if (!isset($orderIds[0]) || !Mage::helper('shopgate_cloudapi/request')->isShopgateRequest()) {
            return $this;
        }

        $newOrderId = $orderIds[0];
        $layout     = Mage::app()->getLayout();
        /** @var Shopgate_Cloudapi_Block_PipelineRequest $pipelineRequestBlock */
        $pipelineRequestBlock = $layout->createBlock('shopgate_cloudapi/pipelineRequest');
        $pipelineRequestBlock->addMethod(
            array(
                //@todo-sg: evaluate the right serial
                'serial' => Shopgate_Cloudapi_Block_PipelineRequest::PIPELINE_REQUEST_SERIAL,
                'name'   => Shopgate_Cloudapi_Block_PipelineRequest::PIPELINE_REQUEST_CREATE_NEW_CART_FOR_CUSTOMER,
                'input'  => array(
                    'orderId' => $newOrderId
                )
            )
        );
        $pipelineRequestBlock->setTemplate('shopgate/cloudapi/pipelineRequest.phtml');
        /** @var Mage_Page_Block_Html_Head $head */
        $head = $layout->getBlock('head');
        $head->addJs('shopgate/pipelineRequest.js');
        $head->append($pipelineRequestBlock);

        /** @var Shopgate_Cloudapi_Block_Analytics_LogPurchase $analyticsLogPurchaseBlock */
        $analyticsLogPurchaseBlock = $layout->createBlock('shopgate_cloudapi/analytics_logPurchase');
        $analyticsLogPurchaseBlock->setOrderId($newOrderId);
        $analyticsLogPurchaseBlock->setTemplate('shopgate/cloudapi/analyticsLogPurchase.phtml');
        $head->addJs('shopgate/analyticsLogPurchase.js');
        $head->append($analyticsLogPurchaseBlock);
    }
}
