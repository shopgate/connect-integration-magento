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
        $result = new Varien_Object();
        $result->setType(Shopgate_Cloudapi_Model_Order_Source::SOURCE_WEBCHECKOUT);
        $result->setId($order->getId());
        $result->setAffiliation($order->getStoreName());
        $result->setRevenueGross($this->formatPrice($order->getGrandTotal()));
        $result->setRevenueNet($this->formatPrice($order->getGrandTotal() - $order->getTaxAmount()));
        $result->setTax($this->formatPrice($order->getTaxAmount()));
        $result->setShippingGross($this->formatPrice($order->getShippingAmount()));
        $result->setShippingNet($this->formatPrice($order->getShippingAmount() - $order->getShippingTaxAmount()));
        $result->setCurrency($order->getStoreCurrencyCode());
        $result->setSuccess(true);

        $resultOrderItems = array();
        foreach ($order->getAllItems() as $item) {
            /** @var Mage_Sales_Model_Order_Item $item */
            $resultOrderItem = new Varien_Object();
            $resultOrderItem->setType('product');
            $resultOrderItem->setName($item->getName());
            $resultOrderItem->setId($item->getId());
            $resultOrderItem->setPriceNet($this->formatPrice($item->getPriceInclTax()));
            $resultOrderItem->setPriceGross($this->formatPrice($item->getPrice()));
            $resultOrderItem->setQuantity($item->getQtyOrdered());
            array_push($resultOrderItems, $resultOrderItem->getData());
        }

        $result->setItems($resultOrderItems);
        $result->setBlacklist(false);
        $result->setTrackers(array());

        return json_encode($result->getData());
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