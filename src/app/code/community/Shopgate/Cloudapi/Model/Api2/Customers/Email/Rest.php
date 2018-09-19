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

class Shopgate_Cloudapi_Model_Api2_Customers_Email_Rest extends Shopgate_Cloudapi_Model_Api2_Customers_Rest
{
    /**
     * Load customer by id
     *
     * @param string|int $id
     *
     * @return Mage_Customer_Model_Customer
     * @throws Mage_Api2_Exception
     * @throws Exception
     */
    protected function loadCustomerById($id)
    {
        /* @var $customer Mage_Customer_Model_Customer */
        $customer = parent::loadCustomerById($id);
        if ($this->getApiUser()->getUserId() !== $customer->getId()) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }

        return $customer;
    }
}
