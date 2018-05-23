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

class Shopgate_Cloudapi_Model_Api2_Carts_Items_Type_Product extends Shopgate_Cloudapi_Model_Api2_Carts_Items_Type_Entry
{

    /**
     * Adds product(s) to cart
     *
     * @param string $cartId
     *
     * @throws Mage_Api2_Exception
     * @throws Mage_Api_Exception
     * @throws Mage_Core_Exception
     */
    public function add($cartId)
    {
        $this->validateProductData();
        $this->call('add', $cartId, $this->getData(), $this->store);
    }

    /**
     * Updates an existing cart item
     *
     * @param string $cartId
     * @param string $cartItemId
     *
     * @throws Mage_Api2_Exception
     * @throws Mage_Api_Exception
     * @throws Mage_Core_Exception
     */
    public function update($cartId, $cartItemId)
    {
        $quote = Mage::getModel('sales/quote')->setStore($this->store)->loadActive($cartId);
        $item  = $quote->getItemById($cartItemId);
        if ($item->getParentItem()) {
            $item = $item->getParentItem();
        }

        $data = $this->getData();
        if (!isset($data['qty'])) {
            throw new Mage_Api2_Exception('Qty not supplied', Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        }

        $item->setQty($data['qty']);
        if ($quote->getShippingAddress()) {
            $quote->getShippingAddress()->setCollectShippingRates(true);
        }

        $quote->collectTotals()->save();
    }

    /**
     * Manipulates a product in quote
     *
     * @param string                               $action - action to call: add, update, remove
     * @param int | string                         $quoteId
     * @param array                                $productData
     * @param int | string | Mage_Core_Model_Store $store
     *
     * @throws Mage_Api2_Exception
     * @throws Mage_Api_Exception
     * @throws Mage_Core_Exception
     */
    protected function call($action, $quoteId, $productData, $store)
    {
        Mage::getModel('checkout/cart_product_api_v2')->{$action}($quoteId, array($productData), $store);
    }

    /**
     * Makes sure that we have a valid product
     * before making the call
     *
     * @throws Mage_Api_Exception
     * @throws Mage_Api2_Exception
     */
    private function validateProductData()
    {
        $data = $this->getData();
        if (isset($data['product_id'])) {
            $productId      = $data['product_id'];
            $identifierType = 'id';
        } elseif (isset($data['sku'])) {
            $productId      = $data['sku'];
            $identifierType = 'sku';
        } else {
            throw new Mage_Api_Exception('add_product_fault');
        }
        $product = Mage::helper('catalog/product')->getProduct($productId, $this->store->getId(), $identifierType);
        if (!$product->getId()) {
            throw new Mage_Api2_Exception("Product '{$productId}' not found", Mage_Api2_Model_Server::HTTP_NOT_FOUND);
        }
    }
}
