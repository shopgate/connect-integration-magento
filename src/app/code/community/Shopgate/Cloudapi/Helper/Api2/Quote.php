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
    const KEY_ITEMS         = 'items';
    const KEY_TOTALS        = 'totals';
    const KEY_ITEM_ERRORS   = 'errors';
    const KEY_ORDER_OPTIONS = 'options';

    /**
     * Adds errors to quote items
     *
     * @param Mage_Sales_Model_Quote $quote
     */
    public function addItemErrors(Mage_Sales_Model_Quote $quote)
    {
        /** @var Mage_Sales_Model_Quote_Item $item */
        foreach ($quote->getAllItems() as $item) {
            if ($item->getData('has_error')) {
                $item->setData(self::KEY_ITEM_ERRORS, $item->getMessage(false));
            }
        }
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
        $quote->setData(
            self::KEY_TOTALS,
            array_map(
                function (Mage_Sales_Model_Quote_Address_Total $total) {
                    return $total->getData();
                },
                $quote->getTotals()
            )
        );
    }
}
