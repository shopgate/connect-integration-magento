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

abstract class Shopgate_Cloudapi_Model_Api2_Carts_Items_Type_Entry
{
    /** @var Mage_Core_Model_Store $store */
    protected $store;
    /** @var array $data */
    protected $data;

    /**
     * Sets a store for this item
     *
     * @param Mage_Core_Model_Store $store
     *
     * @return Shopgate_Cloudapi_Model_Api2_Carts_Items_Type_Entry
     */
    public function setStore(Mage_Core_Model_Store $store)
    {
        $this->store = $store;

        return $this;
    }

    /**
     * Contains item data
     *
     * @param array $data
     *
     * @return Shopgate_Cloudapi_Model_Api2_Carts_Items_Type_Entry
     */
    public function setData(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get the data of this entry
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Adds an item to cart
     *
     * @param string $cartId
     */
    abstract public function add($cartId);

    /**
     * Updates an existing cart item
     *
     * @param string $cartId
     * @param string $cartItemId
     */
    abstract public function update($cartId, $cartItemId);
}
