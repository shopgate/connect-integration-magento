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

/**
 * Shamelessly copied from magento code as we cannot call it directly
 */
abstract class Shopgate_Cloudapi_Model_Api2_Products_Rest extends Shopgate_Cloudapi_Model_Api2_Resource
{
    /**
     * Current loaded product
     *
     * @var Mage_Catalog_Model_Product
     */
    protected $product;

    /** @noinspection PhpHierarchyChecksInspection */
    /**
     * Retrieve product data
     *
     * @return array
     */
    protected function _retrieve()
    {
        $product = $this->getProduct();
        $this->prepareProductForResponse($product);

        return $product->toArray();
    }

    /** @noinspection PhpHierarchyChecksInspection */
    /**
     * Retrieve list of products
     *
     * @return array
     */
    protected function _retrieveCollection()
    {
        /** @var $collection Mage_Catalog_Model_Resource_Product_Collection */
        $collection           = Mage::getResourceModel('catalog/product_collection');
        $store                = $this->_getStore();
        $entityOnlyAttributes = $this->getEntityOnlyAttributes(
            $this->getUserType(),
            Mage_Api2_Model_Resource::OPERATION_ATTRIBUTE_READ
        );
        $availableAttributes  = array_keys(
            $this->getAvailableAttributes(
                $this->getUserType(),
                Mage_Api2_Model_Resource::OPERATION_ATTRIBUTE_READ
            )
        );
        // available attributes not contain image attribute, but it needed for get image_url
        $availableAttributes[] = 'image';
        $collection->addStoreFilter($store->getId())
                   ->addPriceData($this->getCustomerGroupId(), $store->getWebsiteId())
                   ->addAttributeToSelect(array_diff($availableAttributes, $entityOnlyAttributes))
                   ->addAttributeToFilter(
                       'visibility', array(
                                       'neq' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE
                                   )
                   )
                   ->addAttributeToFilter('status', array('eq' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED));
        $this->applyCategoryFilter($collection);
        $this->_applyCollectionModifiers($collection);
        $products = $collection->load();

        /** @var Mage_Catalog_Model_Product $product */
        foreach ($products as $product) {
            $this->setProduct($product);
            $this->prepareProductForResponse($product);
        }

        return $products->toArray();
    }

    /**
     * Apply filter by category id
     *
     * @param Mage_Catalog_Model_Resource_Product_Collection $collection
     */
    protected function applyCategoryFilter(Mage_Catalog_Model_Resource_Product_Collection $collection)
    {
        $categoryId = $this->getRequest()->getParam('category_id');
        if ($categoryId) {
            $category = $this->getCategoryById($categoryId);
            if (!$category->getId()) {
                $this->_critical('Category not found.', Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
            }
            $collection->addCategoryFilter($category);
        }
    }

    /**
     * Add special fields to product get response
     *
     * @param Mage_Catalog_Model_Product $product
     */
    protected function prepareProductForResponse(Mage_Catalog_Model_Product $product)
    {
        /** @var $productHelper Mage_Catalog_Helper_Product */
        $productHelper = Mage::helper('catalog/product');
        $data          = $product->getData();
        $product->setData('website_id', $this->_getStore()->getWebsiteId());
        // customer group is required in product for correct prices calculation
        $product->setData('customer_group_id', $this->getCustomerGroupId());
        // calculate prices
        /** @var float $price */
        $price                             = $product->getPrice();
        $finalPrice                        = $product->getFinalPrice();
        $data['regular_price_with_tax']    = $this->applyTaxToPrice($price, true);
        $data['regular_price_without_tax'] = $this->applyTaxToPrice($price, false);
        $data['final_price_with_tax']      = $this->applyTaxToPrice($finalPrice, true);
        $data['final_price_without_tax']   = $this->applyTaxToPrice($finalPrice, false);
        $data['is_saleable']               = $product->getIsSalable();

        $data['image_url'] = (string)Mage::helper('catalog/image')->init($product, 'image');

        if ($this->getActionType() == self::ACTION_TYPE_ENTITY) {
            $data['url'] = $productHelper->getProductUrl($product->getId());
            /** @var $cartHelper Mage_Checkout_Helper_Cart */
            $cartHelper          = Mage::helper('checkout/cart');
            $data['buy_now_url'] = $cartHelper->getAddUrl($product);

            $stockItem = $product->getData('stock_item');
            if (!$stockItem) {
                $stockItem = Mage::getModel('cataloginventory/stock_item');
                $stockItem->loadByProduct($product);
            }
            $data['is_in_stock'] = $stockItem->getIsInStock();

            /** @var $reviewModel Mage_Review_Model_Review */
            $reviewModel                 = Mage::getModel('review/review');
            $data['total_reviews_count'] = $reviewModel->getTotalReviews(
                $product->getId(), true,
                $this->_getStore()->getId()
            );

            $data['tier_price']         = $this->getTierPrices();
            $data['has_custom_options'] = count($product->getOptions()) > 0;
            /** @var Shopgate_Cloudapi_Helper_Product_Data $cloudApiProductHelper */
            $cloudApiProductHelper  = Mage::helper('shopgate_cloudapi/product_data');
            $data['custom_options'] = $cloudApiProductHelper->getOptions($product);

            switch ($product->getTypeId()) {
                case Mage_Catalog_Model_Product_Type_Configurable::TYPE_CODE:
                    /** @var Shopgate_Cloudapi_Helper_Product_Configurable $cloudApiProductHelper */
                    $cloudApiProductHelper = Mage::helper('shopgate_cloudapi/product_configurable');
                    $data['children']      =
                        $cloudApiProductHelper->getBuyOptions($product, $this->getApiUser()->getUserId());
                    break;
            }
        } else {
            // remove tier price from response
            $product->unsetData('tier_price');
            unset($data['tier_price']);
        }

        $product->addData($data);

        /**
         * Remove cache entries
         */
        foreach ($product->getData() as $key => $value) {
            if (strpos($key, '_cache') === 0) {
                $product->unsetData($key);
            }
        }
    }

    /**
     * Load product by its SKU or ID provided in request
     *
     * @return Mage_Catalog_Model_Product
     * @throws Mage_Api2_Exception
     * @throws Exception
     */
    protected function getProduct()
    {
        if (null === $this->product) {
            $productId = $this->getRequest()->getParam('id');
            /** @var $productHelper Mage_Catalog_Helper_Product */
            $productHelper = Mage::helper('catalog/product');
            $product       = $productHelper->getProduct($productId, $this->_getStore()->getId());
            if (!$product->getId()) {
                $this->_critical(self::RESOURCE_NOT_FOUND);
            }
            // check if product belongs to website current
            if ($this->_getStore()->getId()) {
                $isValidWebsite = in_array($this->_getStore()->getWebsiteId(), $product->getWebsiteIds(), false);
                if (!$isValidWebsite) {
                    $this->_critical(self::RESOURCE_NOT_FOUND);
                }
            }
            // check if product assigned to any website and can be shown
            if (!$productHelper->canShow($product)
                || (!Mage::app()->isSingleStoreMode() && !count($product->getWebsiteIds()))
            ) {
                $this->_critical(self::RESOURCE_NOT_FOUND);
            }
            $this->product = $product;
        }

        return $this->product;
    }

    /**
     * Set product
     *
     * @param Mage_Catalog_Model_Product $product
     */
    protected function setProduct(Mage_Catalog_Model_Product $product)
    {
        $this->product = $product;
    }

    /**
     * Load category by id
     *
     * @param int $categoryId
     *
     * @return Mage_Catalog_Model_Category
     */
    protected function getCategoryById($categoryId)
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return Mage::getModel('catalog/category')->load($categoryId);
    }

    /**
     * Get product price with all tax settings processing
     *
     * @param float                            $price            inputted product price
     * @param bool                             $includingTax     return price include tax flag
     * @param null|Mage_Customer_Model_Address $shippingAddress
     * @param null|Mage_Customer_Model_Address $billingAddress
     * @param null|int                         $ctc              customer tax class
     * @param bool                             $priceIncludesTax flag that price parameter contain tax
     *
     * @return float
     * @see Mage_Tax_Helper_Data::getPrice()
     */
    protected function getPrice(
        $price, $includingTax = null, $shippingAddress = null,
        $billingAddress = null, $ctc = null, $priceIncludesTax = null
    ) {
        $product = $this->getProduct();
        $store   = $this->_getStore();

        if (is_null($priceIncludesTax)) {
            /** @var $config Mage_Tax_Model_Config */
            $config           = Mage::getSingleton('tax/config');
            $priceIncludesTax = $config->priceIncludesTax($store) || $config->getNeedUseShippingExcludeTax();
        }

        $percent          = $product->getData('tax_percent');
        $includingPercent = null;

        $taxClassId = $product->getData('tax_class_id');
        if (is_null($percent)) {
            if ($taxClassId) {
                $request = Mage::getSingleton('tax/calculation')
                               ->getRateRequest($shippingAddress, $billingAddress, $ctc, $store);
                $percent = Mage::getSingleton('tax/calculation')->getRate(
                    $request->setData('product_class_id', $taxClassId)
                );
            }
        }
        if ($taxClassId && $priceIncludesTax) {
            $taxHelper = Mage::helper('tax');
            if ($taxHelper->isCrossBorderTradeEnabled($store)) {
                $includingPercent = $percent;
            } else {
                $request          = Mage::getSingleton('tax/calculation')->getDefaultRateRequest($store);
                $includingPercent = Mage::getSingleton('tax/calculation')
                                        ->getRate($request->setData('product_class_id', $taxClassId));
            }
        }

        if ($percent === false || is_null($percent)) {
            if ($priceIncludesTax && !$includingPercent) {
                return $price;
            }
        }
        $product->setData('tax_percent', $percent);

        if (!is_null($includingTax)) {
            if ($priceIncludesTax) {
                if ($includingTax) {
                    /**
                     * Recalculate price include tax in case of different rates
                     */
                    if ($includingPercent != $percent) {
                        $price = $this->calculatePrice($price, $includingPercent, false);
                        /**
                         * Using regular rounding. Ex:
                         * price incl tax   = 52.76
                         * store tax rate   = 19.6%
                         * customer tax rate= 19%
                         *
                         * price excl tax = 52.76 / 1.196 = 44.11371237 ~ 44.11
                         * tax = 44.11371237 * 0.19 = 8.381605351 ~ 8.38
                         * price incl tax = 52.49531773 ~ 52.50 != 52.49
                         *
                         * that why we need round prices excluding tax before applying tax
                         * this calculation is used for showing prices on catalog pages
                         */
                        if ($percent != 0) {
                            $price = Mage::getSingleton('tax/calculation')->round($price);
                            $price = $this->calculatePrice($price, $percent, true);
                        }
                    }
                } else {
                    $price = $this->calculatePrice($price, $includingPercent, false);
                }
            } else {
                if ($includingTax) {
                    $price = $this->calculatePrice($price, $percent, true);
                }
            }
        } else {
            if ($priceIncludesTax) {
                if ($includingTax) {
                    $price = $this->calculatePrice($price, $includingPercent, false);
                    $price = $this->calculatePrice($price, $percent, true);
                } else {
                    $price = $this->calculatePrice($price, $includingPercent, false);
                }
            } else {
                if ($includingTax) {
                    $price = $this->calculatePrice($price, $percent, true);
                }
            }
        }

        return $store->roundPrice($price);
    }

    /**
     * Calculate price including/excluding tax base on tax rate percent
     *
     * @param float $price
     * @param float $percent
     * @param bool  $includeTax true - for calculate price including tax and false if price excluding tax
     *
     * @return float
     */
    protected function calculatePrice($price, $percent, $includeTax)
    {
        /** @var Mage_Tax_Model_Calculation $calculator */
        $calculator = Mage::getSingleton('tax/calculation');
        $taxAmount  = $calculator->calcTaxAmount($price, $percent, !$includeTax, false);

        return $includeTax ? $price + $taxAmount : $price - $taxAmount;
    }

    /**
     * Retrieve tier prices in special format
     *
     * @return array
     */
    protected function getTierPrices()
    {
        $tierPrices = array();
        /** @noinspection PhpWrongForeachArgumentTypeInspection */
        foreach ($this->getProduct()->getTierPrice() as $tierPrice) {
            $tierPrices[] = array(
                'qty'               => $tierPrice['price_qty'],
                'price_with_tax'    => $this->applyTaxToPrice($tierPrice['price']),
                'price_without_tax' => $this->applyTaxToPrice($tierPrice['price'], false)
            );
        }

        return $tierPrices;
    }

    /**
     * Default implementation. May be different for customer/guest/admin role.
     *
     * @return int
     */
    abstract protected function getCustomerGroupId();

    /**
     * Default implementation. May be different for customer/guest/admin role.
     *
     * @param float $price
     * @param bool  $withTax
     *
     * @return float
     */
    protected function applyTaxToPrice(
        $price, /** @noinspection PhpUnusedParameterInspection */
        $withTax = true
    ) {
        return $price;
    }
}
