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

class Shopgate_Cloudapi_Block_Checkout_Onepage_Success extends Mage_Core_Block_Template
{
    /**
     * @var int
     */
    protected $orderId;

    /**
     * @return int
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

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
    public function getShopBaseUrl()
    {
        return $this->getUrl();
    }

    /**
     * @return bool
     */
    public function isShopgateCheckout()
    {
        return $this->getRequestHelper()->isShopgateCheckout();
    }

    /**
     * @return Shopgate_Cloudapi_Helper_Request
     */
    protected function getRequestHelper()
    {
        return Mage::helper('shopgate_cloudapi/request');
    }

    /**
     * @return string
     */
    public function getJsonOrderData()
    {
        /** @var Mage_Sales_Model_Order $order */
        $order = Mage::getModel('sales/order')->load($this->getOrderId());
        /** @var Shopgate_Cloudapi_Model_Frontend_Analytics_LogPurchase $logPurchase */
        $logPurchase = Mage::getModel('shopgate_cloudapi/frontend_analytics_logPurchase');
        $logPurchase->setType(Shopgate_Cloudapi_Model_Order_Source::SOURCE_WEBCHECKOUT)
                    ->setId($order->getId())
                    ->setAffiliation($order->getStoreName())
                    ->setRevenueGross($this->formatPrice($order->getGrandTotal()))
                    ->setRevenueNet($this->formatPrice($order->getGrandTotal() - $order->getTaxAmount()))
                    ->setTax($this->formatPrice($order->getTaxAmount()))
                    ->setShippingGross($this->formatPrice($order->getShippingAmount()))
                    ->setShippingNet(
                        $this->formatPrice($order->getShippingAmount() - $order->getShippingTaxAmount())
                    )
                    ->setCurrency($order->getStoreCurrencyCode())
                    ->setSuccess(true)
                    ->setBlacklist(false)
                    ->setTrackers(array());

        foreach ($order->getAllItems() as $item) {
            /** @var Mage_Sales_Model_Order_Item $item */
            /** @var Shopgate_Cloudapi_Model_Frontend_Analytics_LogPurchase_Item $logPurchaseItem */
            $logPurchaseItem = Mage::getModel('shopgate_cloudapi/frontend_analytics_logPurchase_item')
                                   ->setId($item->getId())
                                   ->setType('product')
                                   ->setName($item->getName())
                                   ->setPriceNet($this->formatPrice($item->getPriceInclTax()))
                                   ->setPriceGross($this->formatPrice($item->getPrice()))
                                   ->setQuantity($item->getQtyOrdered());
            $logPurchase->addItem($logPurchaseItem);
        }

        return $logPurchase->getJsonData();
    }

    /**
     * @param int $price
     *
     * @return string
     */
    protected function formatPrice($price)
    {
        return sprintf('%.2f', $price);
    }
}