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

class Shopgate_Cloudapi_Model_Acl_Customers_Filter extends Shopgate_Cloudapi_Model_Acl_Filter
{
    /**
     * @inheritdoc
     */
    public function in(array $requestData)
    {
        $dateAttributes = Mage::getModel('customer/attribute')
                             ->getCollection()
                             ->addFilter('frontend_input', 'date');
        foreach ($dateAttributes as $dateAttribute) {
            $code = $dateAttribute->getAttributeCode();
            if (isset($requestData[$code])) {
                /** Converting date to locale defined */
                $date = Mage::app()->getLocale()->date($requestData[$code], null, null, false);
                $requestData[$code] = $date->toString();
            };
        }

        return parent::in($requestData);
    }
}
