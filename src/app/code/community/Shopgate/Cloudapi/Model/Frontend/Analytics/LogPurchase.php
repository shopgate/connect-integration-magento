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
 * @method $this setType(string $type)
 * @method string getType()
 * @method $this setId(int $id)
 * @method int getId()
 * @method $this setAffiliation(string $affiliation)
 * @method string getAffiliation()
 * @method $this setRevenueGross(float $revenueGross)
 * @method float getRevenueGross()
 * @method $this setRevenueNet(float $revenueNet)
 * @method float getRevenueNet()
 * @method $this setTax(float $tax)
 * @method float getTax()
 * @method $this setShippingGross(float $shippingGross)
 * @method float getShippingGross()
 * @method $this setShippingNet(float $shippingNet)
 * @method float getShippingNet()
 * @method $this setCurrency(string $currency)
 * @method string getCurrency()
 * @method $this setSuccess(bool $success)
 * @method bool getSuccess()
 * @method $this setBlacklist(bool $blacklist)
 * @method bool getBlacklist()
 * @method $this setTrackers(array $trackers)
 * @method array getTrackers()
 * @method $this setItems(Shopgate_Cloudapi_Model_Frontend_Analytics_LogPurchase_Item[] $items)
 * @method array getItems()
 */
class Shopgate_Cloudapi_Model_Frontend_Analytics_LogPurchase extends Varien_Object
{

    /**
     * @param Shopgate_Cloudapi_Model_Frontend_Analytics_LogPurchase_Item $item
     */
    public function addItem(Shopgate_Cloudapi_Model_Frontend_Analytics_LogPurchase_Item $item)
    {
        $this->_data['items'][] = $item->getData();
    }

    /**
     * @return string
     */
    public function getJsonData()
    {
        return json_encode($this->getData());
    }
}
