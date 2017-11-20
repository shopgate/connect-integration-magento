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

class Shopgate_Cloudapi_Model_Api2_Carts_Items_Type_Coupon extends Shopgate_Cloudapi_Model_Api2_Carts_Items_Type_Entry
{
    const KEY = 'couponCode';

    /**
     * @inheritdoc
     * @throws Mage_Api2_Exception
     */
    public function add($cartId)
    {
        Mage::getModel('checkout/cart_coupon_api_v2')->add($cartId, $this->getData(), $this->store);
    }

    /**
     * @inheritdoc
     * @throws Mage_Api2_Exception
     */
    public function update($cartId, $cartItemId)
    {
        $this->add($cartId);
    }

    /**
     * @return array
     * @throws Mage_Api2_Exception
     */
    public function getData()
    {
        $this->validate();

        return $this->data[self::KEY];
    }

    /**
     * Test that the data object is formatted to our specifications
     *
     * @throws Mage_Api2_Exception
     */
    private function validate()
    {
        if (!isset($this->data[self::KEY])) {
            throw new Mage_Api2_Exception('Incorrect request given', Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        }
    }
}
