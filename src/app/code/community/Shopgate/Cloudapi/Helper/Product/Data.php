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

class Shopgate_Cloudapi_Helper_Product_Data extends Mage_Core_Helper_Abstract
{
    /** @var Mage_Catalog_Model_Product */
    protected $product;

    /**
     * @param Mage_Catalog_Model_Product $product
     *
     * @return array
     */
    public function getOptions($product)
    {
        $optionsResult = array();

        foreach ($product->getOptions() as $optionData) {
            /** @var Mage_Catalog_Model_Product_Option $optionData */
            $data    = $optionData->getData();
            $options = array();
            foreach ($optionData->getValues() as $value) {
                /** @var Mage_Catalog_Model_Product_Option_Value $value */
                array_push($options, $value->getData());
            }

            $data['values'] = $options;
            array_push($optionsResult, $data);
        }

        return $optionsResult;
    }

    /**
     * Convert price from default currency to current currency
     *
     * @param float    $price
     * @param boolean  $round
     * @param null|int $storeId
     *
     * @return float
     */
    public function convertPrice($price, $round = false, $storeId = null)
    {
        if (empty($price)) {
            return 0;
        }

        $price = $this->getCurrentStore($storeId)->convertPrice($price);
        if ($round) {
            $price = $this->getCurrentStore($storeId)->roundPrice($price);
        }

        return $price;
    }

    /**
     * Calculation real price
     *
     * @param float    $price
     * @param bool     $isPercent
     * @param null|int $storeId
     *
     * @return mixed
     */
    public function preparePrice($price, $isPercent = false, $storeId = null)
    {
        if ($isPercent && !empty($price)) {
            $price = $this->product->getFinalPrice() * $price / 100;
        }

        return $this->convertPrice($price, true, $storeId);
    }

    /**
     * Calculation price before special price
     *
     * @param float    $price
     * @param bool     $isPercent
     * @param null|int $storeId
     *
     * @return mixed
     */
    public function prepareOldPrice($price, $isPercent = false, $storeId = null)
    {
        if ($isPercent && !empty($price)) {
            $price = $this->product->getPrice() * $price / 100;
        }

        return $this->convertPrice($price, true, $storeId);
    }

    /**
     * Validating of super product option value
     *
     * @param string $attributeId
     * @param array  $value
     * @param array  $options
     *
     * @return boolean
     */
    protected function validateAttributeValue($attributeId, &$value, &$options)
    {
        if (isset($options[$attributeId][$value['value_index']])) {
            return true;
        }

        return false;
    }

    /**
     * Validation of super product option
     *
     * @param array $info
     *
     * @return boolean
     */
    protected function validateAttributeInfo(&$info)
    {
        if (count($info['options']) > 0) {
            return true;
        }

        return false;
    }

    /**
     * Get Allowed Products
     *
     * @return array
     */
    protected function getAllowProducts()
    {
        $products          = array();
        $skipSaleableCheck = Mage::helper('catalog/product')->getSkipSaleableCheck();
        /** @var Mage_Catalog_Model_Product_Type_Configurable | Mage_Catalog_Model_Product_Type_Simple $type */
        $type        = $this->getProduct()->getTypeInstance(true);
        $allProducts = $type->getUsedProducts(null, $this->getProduct());

        foreach ($allProducts as $product) {
            /** @var Mage_Catalog_Model_Product $product */
            /** @noinspection PhpUndefinedMethodInspection */
            if ($product->isSaleable()
                || $skipSaleableCheck
                || (!$product->getStockItem()->getIsInStock()
                    && Mage::helper('cataloginventory')->isShowOutOfStock())
            ) {
                $products[] = $product;
            }
            $products[] = $product;
        }

        return $products;
    }

    /**
     * @return Mage_Catalog_Model_Product
     */
    protected function getProduct()
    {
        return $this->product;
    }

    /**
     * Get allowed attributes
     *
     * @return array
     */
    public function getAllowAttributes()
    {
        /** @var Mage_Catalog_Model_Product_Type_Configurable | Mage_Catalog_Model_Product_Type_Simple $type */
        $type = $this->getProduct()->getTypeInstance(true);

        return $type->getConfigurableAttributes($this->getProduct());
    }

    /**
     * Retrieve current store
     *
     * @param null $storeId
     *
     * @return Mage_Core_Model_Store
     */
    public function getCurrentStore($storeId = null)
    {
        return Mage::app()->getStore($storeId);
    }
}
