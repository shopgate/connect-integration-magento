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
 * @method setType(string $type)
 * @method setId(int $id)
 * @method setAffiliation(string $affiliation)
 * @method setRevenueGross(float $revenueGross)
 * @method setRevenueNet(float $revenueNet)
 * @method setTax(float $tax)
 * @method setShippingGross(float $shippingGross)
 * @method setShippingNet(float $shippingNet)
 * @method setCurrency(string $currency)
 * @method setSuccess(bool $success)
 * @method setBlacklist(bool $blacklist)
 * @method setTrackers(array $trackers)
 * @method setItems(Shopgate_Cloudapi_Model_Pipeline_AnalyticsLogPurchase_Item [] $items)
 */
class Shopgate_Cloudapi_Model_Pipeline_AnalyticsLogPurchase extends Varien_Object
{
    /**
     * @var array
     */
    protected $items = array();

    /**
     * @param Shopgate_Cloudapi_Model_Pipeline_AnalyticsLogPurchase_Item $item
     */
    public function addItem($item)
    {
        $this->items[] = $item->getData();
    }

    /**
     * @return string
     */
    public function getJsonData()
    {
        $this->setItems($this->items);

        return json_encode($this->getData());
    }
}
