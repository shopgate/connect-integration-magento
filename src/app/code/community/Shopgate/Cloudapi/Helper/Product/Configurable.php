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

class Shopgate_Cloudapi_Helper_Product_Configurable extends Shopgate_Cloudapi_Helper_Product_Data
{

    /**
     * @param Mage_Catalog_Model_Product $product
     *
     * @param string                     $userId
     *
     * @return array
     * @todo-sg: use native 1.9.2 code if it exists, fallback to this function if not
     * @todo-sg: refactor
     */
    public function getBuyOptions($product, $userId)
    {
        $this->product = $product;

        $attributes = array();
        $options    = array();
        $taxHelper  = Mage::helper('tax');

        $preConfiguredFlag = $this->getProduct()->hasData('preconfigured_values');
        if ($preConfiguredFlag) {
            $preConfiguredValues = $this->getProduct()->getPreconfiguredValues();
            $defaultValues       = array();
        }

        $productStock = array();
        foreach ($this->getAllowProducts() as $product) {
            /** @var Mage_Catalog_Model_Product $product */
            $productId = $product->getId();
            /** @var Mage_CatalogInventory_Model_Stock_Item $stockItem */
            $stockItem                = $product->getData('stock_item');
            $productStock[$productId] = $stockItem->getIsInStock();
            /** @var Mage_Catalog_Model_Product_Type_Configurable_Attribute $attribute */
            foreach ($this->getAllowAttributes() as $attribute) {
                /** @var Mage_Catalog_Model_Resource_Eav_Attribute $productAttribute */
                $productAttribute   = $attribute->getData('product_attribute');
                $productAttributeId = $productAttribute->getId();
                $attributeValue     = $product->getData($productAttribute->getAttributeCode());
                if (!isset($options[$productAttributeId])) {
                    $options[$productAttributeId] = array();
                }

                if (!isset($options[$productAttributeId][$attributeValue])) {
                    $options[$productAttributeId][$attributeValue] = array();
                }
                $options[$productAttributeId][$attributeValue][] = $productId;
            }
        }

        /** @var Mage_Catalog_Model_Product_Type_Configurable_Attribute $attribute */
        foreach ($this->getAllowAttributes() as $attribute) {
            /** @var Mage_Catalog_Model_Resource_Eav_Attribute $productAttribute */
            $productAttribute = $attribute->getData('product_attribute');
            $attributeId      = $productAttribute->getId();
            $info             = array(
                'id'      => $productAttribute->getId(),
                'code'    => $productAttribute->getAttributeCode(),
                'label'   => $attribute->getLabel(),
                'options' => array()
            );

            $optionPrices = array();
            $prices       = $attribute->getData('prices');
            if (is_array($prices)) {
                foreach ($prices as $value) {
                    if (!$this->validateAttributeValue($attributeId, $value, $options)) {
                        continue;
                    }
                    $this->getProduct()->setData(
                        'configurable_price',
                        $this->preparePrice($value['pricing_value'], $value['is_percent'])
                    );
                    $this->getProduct()->setData('parent_id', true);
                    Mage::dispatchEvent(
                        'catalog_product_type_configurable_price',
                        array('product' => $this->getProduct())
                    );
                    $configurablePrice = $this->getProduct()->getData('configurable_price');

                    if (isset($options[$attributeId][$value['value_index']])) {
                        $productsIndexOptions = $options[$attributeId][$value['value_index']];
                        $productsIndex        = array();
                        foreach ($productsIndexOptions as $productIndex) {
                            if ($productStock[$productIndex]) {
                                $productsIndex[] = $productIndex;
                            }
                        }
                    } else {
                        $productsIndex = array();
                    }

                    $info['options'][] = array(
                        'id'       => $value['value_index'],
                        'label'    => $value['label'],
                        'price'    => $configurablePrice,
                        'oldPrice' => $this->prepareOldPrice($value['pricing_value'], $value['is_percent']),
                        'products' => $productsIndex,
                    );
                    $optionPrices[]    = $configurablePrice;
                }
            }
            /**
             * Prepare formated values for options choose
             */
            foreach ($optionPrices as $optionPrice) {
                foreach ($optionPrices as $additional) {
                    $this->preparePrice(abs($additional - $optionPrice));
                }
            }
            if ($this->validateAttributeInfo($info)) {
                $attributes[$attributeId] = $info;
            }

            // Add attribute default value (if set)
            if ($preConfiguredFlag) {
                /** @noinspection PhpUndefinedVariableInspection */
                $configValue = $preConfiguredValues->getData('super_attribute/' . $attributeId);
                if ($configValue) {
                    $defaultValues[$attributeId] = $configValue;
                }
            }
        }

        $taxCalculation = Mage::getSingleton('tax/calculation');
        if ($userId > 0) {
            /** @var Mage_Customer_Model_Customer $customer */
            $customer = Mage::getModel('customer/customer')->load($userId);
            $taxCalculation->setCustomer($customer);
        }

        if (method_exists($taxCalculation, 'getDefaultRateRequest')) {
            $_request = $taxCalculation->getDefaultRateRequest();
        } else {
            $_request = $taxCalculation->getRateRequest();
        }

        $_request->setData('product_class_id', $this->getProduct()->getData('tax_class_id'));
        $defaultTax = $taxCalculation->getRate($_request);

        $_request = $taxCalculation->getRateRequest();
        $_request->setData('product_class_id', $this->getProduct()->getData('tax_class_id'));
        $currentTax = $taxCalculation->getRate($_request);

        $taxConfig = array(
            'includeTax'     => $taxHelper->priceIncludesTax(),
            'showIncludeTax' => $taxHelper->displayPriceIncludingTax(),
            'showBothPrices' => $taxHelper->displayBothPrices(),
            'defaultTax'     => $defaultTax,
            'currentTax'     => $currentTax,
            'inclTaxTitle'   => Mage::helper('catalog')->__('Incl. Tax')
        );

        /** @noinspection PhpParamsInspection */
        $config = array(
            'attributes' => $attributes,
            'basePrice'  => $this->convertPrice($this->getProduct()->getFinalPrice()),
            'oldPrice'   => $this->convertPrice($this->getProduct()->getPrice()),
            'productId'  => $this->getProduct()->getId(),
            'chooseText' => Mage::helper('catalog')->__('Choose an Option...'),
            'taxConfig'  => $taxConfig
        );

        if ($preConfiguredFlag && !empty($defaultValues)) {
            $config['defaultValues'] = $defaultValues;
        }

        return $config;
    }
}
