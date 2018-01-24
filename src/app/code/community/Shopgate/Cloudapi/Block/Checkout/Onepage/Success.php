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
        /** @var Shopgate_Cloudapi_Model_Frontend_Checkout_Onepage_Purchase $purchase */
        $purchase = Mage::getModel('shopgate_cloudapi/frontend_checkout_onepage_purchase');
        $purchase->setNumber($order->getId());
        $purchase->setCurrency($order->getOrderCurrencyCode());

        foreach ($order->getAllItems() as $item) {
            /** @var Mage_Sales_Model_Order_Item $item */
            /** @var Shopgate_Cloudapi_Model_Frontend_Checkout_Onepage_Purchase_Product $product */
            $product = Mage::getModel('shopgate_cloudapi/frontend_checkout_onepage_purchase_product');
            $product->setId($item->getId());
            $product->setQuantity($item->getQtyOrdered());
            $product->setName($item->getName());

            $price = Mage::getModel('shopgate_cloudapi/frontend_checkout_onepage_purchase_product_price');
            $price->setData('withTax', $this->formatPrice($item->getPriceInclTax()));
            $price->setData('net', $this->formatPrice($item->getPrice()));
            $product->addPrice($price);
            $purchase->addProduct($product);
        }

        /** @var Shopgate_Cloudapi_Model_Frontend_Checkout_Onepage_Purchase_Total $total */
        $total = Mage::getModel('shopgate_cloudapi/frontend_checkout_onepage_purchase_total');

        $total->setData('type', 'shipping');
        $total->setData('amount', $this->formatPrice($order->getShippingInclTax()));
        $purchase->addTotal($total);

        $total->setData('type', 'tax');
        $total->setData('amount', $this->formatPrice($order->getTaxAmount()));
        $purchase->addTotal($total);

        $total->setData('type', 'grandTotal');
        $total->setData('amount', $this->formatPrice($order->getGrandTotal()));
        $purchase->addTotal($total);

        return $purchase->getJsonData();
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