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
 * @method $this setNumber(string $number)
 * @method string getNUmber()
 * @method $this setCurrency(string $currency)
 * @method string getCurrency()
 */
class Shopgate_Cloudapi_Model_Frontend_Checkout_Onepage_Purchase extends Varien_Object
{
    /**
     * @param Shopgate_Cloudapi_Model_Frontend_Checkout_Onepage_Purchase_Product $product
     */
    public function addProduct(Shopgate_Cloudapi_Model_Frontend_Checkout_Onepage_Purchase_Product $product)
    {
        $this->_data['products'][] = $product->getData();
    }

    /**
     * @param Shopgate_Cloudapi_Model_Frontend_Checkout_Onepage_Purchase_Total $total
     */
    public function addTotal(Shopgate_Cloudapi_Model_Frontend_Checkout_Onepage_Purchase_Total $total)
    {
        $this->_data['totals'][] = $total->getData();
    }

    /**
     * @return string
     */
    public function getJsonData()
    {
        return json_encode(array('order' => $this->getData()));
    }
}
