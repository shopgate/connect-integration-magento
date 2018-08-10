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

class Shopgate_Cloudapi_Model_Observers_AddShopgateCartRule
{
    /**
     * Adds a shopgate cart type to the shopping
     * cart price rule condition list
     *
     * @param Varien_Event_Observer $observer
     *
     * @return Varien_Event_Observer
     */
    public function execute(Varien_Event_Observer $observer)
    {
        $additional = $observer->getData('additional');
        $conditions = (array)$additional->getConditions();

        $conditions = array_merge_recursive(
            $conditions,
            array(
                array(
                    'label' => Mage::helper('shopgate_cloudapi')->__('Shopgate Rules'),
                    'value' => array(
                        array(
                            'label' => Mage::helper('shopgate_cloudapi')->__('Cart Type'),
                            'value' => 'shopgate_cloudapi/salesRule_condition',
                        ),
                    ),
                ),
            )
        );
        $additional->setConditions($conditions);

        return $observer;
    }
}
