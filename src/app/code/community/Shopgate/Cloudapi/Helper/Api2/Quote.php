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

class Shopgate_Cloudapi_Helper_Api2_Quote extends Mage_Core_Helper_Abstract
{
    const KEY_ITEMS                       = 'items';
    const KEY_TOTALS                      = 'totals';
    const KEY_ERROR_MESSAGES              = 'errors';
    const KEY_ERRORS                      = 'has_error';
    const KEY_SHIPPING                    = 'shipping';
    const KEY_CART_PRICE_DISPLAY_SETTINGS = 'cart_price_display_settings';
    const KEY_ORDER_OPTIONS               = 'options';

    /**
     * Adds errors to quote items.
     * Store state switch is necessary as errors do not
     * print in the Admin store state, which is the
     * default global store endpoints run in.
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param Mage_Core_Model_Store  $store
     *
     * @throws Mage_Core_Model_Store_Exception
     */
    public function addItemErrors(Mage_Sales_Model_Quote $quote, Mage_Core_Model_Store $store)
    {
        $adminStore = Mage::app()->getStore();
        Mage::app()->setCurrentStore($store->getCode());
        /** @var Mage_Sales_Model_Quote_Item $item */
        foreach ($quote->getAllItems() as $item) {
            if ($item->getData(self::KEY_ERRORS)) {
                $item->setData(self::KEY_ERROR_MESSAGES, $item->getMessage(false));
            }
        }
        Mage::app()->setCurrentStore($adminStore);
    }

    /**
     * Backup script that sets the app code in case
     * this cart was created without having items
     * added to it via our API.
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param Mage_Core_Model_Store  $store
     *
     * @throws Mage_Core_Model_Store_Exception
     */
    public function setSaleRuleType(Mage_Sales_Model_Quote $quote, Mage_Core_Model_Store $store)
    {
        $collection = Mage::getResourceModel('shopgate_cloudapi/cart_source_collection')->setQuoteFilter($quote->getId());
        if (!$collection->isEmpty()) {
            return;
        }
        $adminStore = Mage::app()->getStore();
        Mage::app()->setCurrentStore($store->getCode());
        /** @var Mage_Sales_Model_Quote_Item $item */
        $this->addAppSaleRuleCondition($quote);
        $quote->collectTotals();
        Mage::app()->setCurrentStore($adminStore);
    }

    /**
     * Adds errors to the quote.
     * Store state switch is necessary as errors do not
     * print in the Admin store state, which is the
     * default global store endpoints run in.
     *
     * Currently only the minimum order amount will be validated.
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param Mage_Core_Model_Store  $store
     *
     * @throws Mage_Core_Model_Store_Exception
     * @throws Zend_Currency_Exception
     */
    public function addQuoteErrors(Mage_Sales_Model_Quote $quote, Mage_Core_Model_Store $store)
    {
        $adminStore = Mage::app()->getStore();
        Mage::app()->setCurrentStore($store->getCode());
        /** @var Mage_Sales_Model_Quote_Item $item */
        if (!$quote->validateMinimumAmount()) {
            $minimumAmount = Mage::app()->getLocale()->currency($store->getCurrentCurrencyCode())
                                 ->toCurrency(Mage::getStoreConfig('sales/minimum_order/amount'));

            $messages[] = Mage::getStoreConfig('sales/minimum_order/description')
                ? : Mage::helper('checkout')->__('Minimum order amount is %s', $minimumAmount);

            $quote->setData(self::KEY_ERRORS, true);
            $quote->setData(self::KEY_ERROR_MESSAGES, $messages);
        }
        Mage::app()->setCurrentStore($adminStore);
    }

    /**
     * Adds item details to quote
     *
     * @param Mage_Sales_Model_Quote $quote
     */
    public function addItems(Mage_Sales_Model_Quote $quote)
    {
        $quote->setData(
            self::KEY_ITEMS,
            array_map(array($this, 'mapQuoteItems'), $quote->getAllItems())
        );
    }

    /**
     * @param $quoteItem Mage_Sales_Model_Quote_Item
     *
     * @return array
     */
    private function mapQuoteItems(Mage_Sales_Model_Quote_Item $quoteItem)
    {
        $orderOptions = $this->getOrderOptions($quoteItem);
        if ($orderOptions->hasData(self::KEY_ORDER_OPTIONS)) {
            $quoteItem->setData(
                self::KEY_ORDER_OPTIONS,
                $orderOptions->getData(self::KEY_ORDER_OPTIONS)
            );
        }

        return $quoteItem->getData();
    }

    /**
     * @param $item Mage_Sales_Model_Quote_Item
     *
     * @return Varien_Object
     */
    private function getOrderOptions(Mage_Sales_Model_Quote_Item $item)
    {
        $result = new Varien_Object();
        $result->setData(
            $item->getProduct()
                ->getTypeInstance(true)
                ->getOrderOptions($item->getProduct())
        );

        return $result;
    }

    /**
     * Adds totals to quote
     *
     * @param Mage_Sales_Model_Quote $quote
     */
    public function addTotals(Mage_Sales_Model_Quote $quote)
    {
        $totals = array_map(
            function (Mage_Sales_Model_Quote_Address_Total $total) {
                return $total->getData();
            },
            $quote->getTotals()
        );

        if (isset($totals[self::KEY_SHIPPING]) && $totals[self::KEY_SHIPPING]['value']) {
            $totals[self::KEY_SHIPPING]['value'] = Mage::helper('tax')->getShippingPrice(
                $totals[self::KEY_SHIPPING]['value'],
                Mage::helper('tax')->displayShippingPriceIncludingTax() || Mage::helper('tax')->displayShippingBothPrices(),
                $quote->getShippingAddress(),
                $quote->getCustomerTaxClassId()
            );
        }

        $quote->setData(
            self::KEY_TOTALS,
            $totals
        );

        if (isset($totals['discount']) && $quote->getCouponCode() === null) {
            $quote->setCouponCode('1');
        }
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     * @param Mage_Core_Model_Store  $store
     */
    public function addCartPriceDisplaySettings(Mage_Sales_Model_Quote $quote, Mage_Core_Model_Store $store)
    {
        $quote->setData(
            self::KEY_CART_PRICE_DISPLAY_SETTINGS,
            Mage::getStoreConfig(Mage_Tax_Model_Config::XML_PATH_DISPLAY_CART_PRICE, $store)
        );
    }

    /**
     * Sets the cart type sale rule condition
     *
     * @param Mage_Sales_Model_Quote $quote
     */
    public function addAppSaleRuleCondition(Mage_Sales_Model_Quote $quote)
    {
        $quote->getShippingAddress()->setData(
            Shopgate_Cloudapi_Model_SalesRule_Condition::CART_TYPE,
            Shopgate_Cloudapi_Model_System_Config_Source_Cart_Types::APP
        );
    }
}
