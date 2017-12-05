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

class Shopgate_Cloudapi_Block_AnalyticsLogPurchase extends Mage_Adminhtml_Block_Template
{
    /**
     * @var int
     */
    protected $orderId;

    /**
     * @param int $orderId
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;
    }

    /**
     * @return string
     */
    public function getJsonOrderData()
    {
        /** @var Mage_Sales_Model_Order $order */
        $order  = Mage::getModel('sales/order')->load($this->orderId);
        /** @var Shopgate_Cloudapi_Model_Pipeline_AnalyticsLogPurchase $analyticsLogPurchase */
        $analyticsLogPurchase = Mage::getModel('shopgate_cloudapi/pipeline_AnalyticsLogPurchase');
        $analyticsLogPurchase->setType(Shopgate_Cloudapi_Model_Order_Source::SOURCE_WEBCHECKOUT);
        $analyticsLogPurchase->setId($order->getId());
        $analyticsLogPurchase->setAffiliation($order->getStoreName());
        $analyticsLogPurchase->setRevenueGross($this->formatPrice($order->getGrandTotal()));
        $analyticsLogPurchase->setRevenueNet($this->formatPrice($order->getGrandTotal() - $order->getTaxAmount()));
        $analyticsLogPurchase->setTax($this->formatPrice($order->getTaxAmount()));
        $analyticsLogPurchase->setShippingGross($this->formatPrice($order->getShippingAmount()));
        $analyticsLogPurchase->setShippingNet(
            $this->formatPrice($order->getShippingAmount() - $order->getShippingTaxAmount())
        );
        $analyticsLogPurchase->setCurrency($order->getStoreCurrencyCode());
        $analyticsLogPurchase->setSuccess(true);
        $analyticsLogPurchase->setBlacklist(false);
        $analyticsLogPurchase->setTrackers(array());

        foreach ($order->getAllItems() as $item) {
            /** @var Mage_Sales_Model_Order_Item $item */
            /** @var Shopgate_Cloudapi_Model_Pipeline_AnalyticsLogPurchase_Item $analyticsLogPurchaseItem */
            $analyticsLogPurchaseItem = Mage::getModel('shopgate_cloudapi/pipeline_AnalyticsLogPurchase_Item');
            $analyticsLogPurchaseItem->setId($item->getId());
            $analyticsLogPurchaseItem->setType('product');
            $analyticsLogPurchaseItem->setName($item->getName());
            $analyticsLogPurchaseItem->setPriceNet($this->formatPrice($item->getPriceInclTax()));
            $analyticsLogPurchaseItem->setPriceGross($this->formatPrice($item->getPrice()));
            $analyticsLogPurchaseItem->setQuantity($item->getQtyOrdered());
            $analyticsLogPurchase->addItem($analyticsLogPurchaseItem);
        }

        return $analyticsLogPurchase->getJsonData();
    }

    /**
     * @param int $price
     * @return string
     */
    protected function formatPrice($price)
    {
        return sprintf("%.2f", $price);
    }
}
