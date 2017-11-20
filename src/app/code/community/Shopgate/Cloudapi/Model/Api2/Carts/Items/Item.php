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

class Shopgate_Cloudapi_Model_Api2_Carts_Items_Item
{
    /** @var string $cartItemId*/
    protected $cartItemId;
    /** @var  Shopgate_Cloudapi_Model_Api2_Carts_Items_Type_Entry $entry */
    protected $entry;

    /**
     * Performs actions on the quote/cart
     *
     * @param string | int $cartId
     */
    public function apply($cartId)
    {
        if (empty($this->cartItemId)) {
            $this->getEntry()->add($cartId);
        } else {
            $this->getEntry()->update($cartId, $this->cartItemId);
        }
    }

    /**
     * @param string $id
     *
     * @return Shopgate_Cloudapi_Model_Api2_Carts_Items_Item
     */
    public function setCartItemId($id)
    {
        $this->cartItemId = $id;

        return $this;
    }

    /**
     * @param Shopgate_Cloudapi_Model_Api2_Carts_Items_Type_Entry $item
     *
     * @return Shopgate_Cloudapi_Model_Api2_Carts_Items_Item
     */
    public function setEntry(Shopgate_Cloudapi_Model_Api2_Carts_Items_Type_Entry $item)
    {
        $this->entry = $item;

        return $this;
    }

    /**
     * @return Shopgate_Cloudapi_Model_Api2_Carts_Items_Type_Entry
     */
    public function getEntry()
    {
        return $this->entry;
    }
}
