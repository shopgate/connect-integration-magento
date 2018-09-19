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

class Shopgate_Cloudapi_Model_Api2_Customers_Email_Validator extends Shopgate_Cloudapi_Model_Api2_Validator
{
    const FIELD_EMAIL = 'email';

    /**
     * Validates the incoming email address data
     *
     * @inheritdoc
     * @throws Zend_Validate_Exception
     */
    public function isValidData(array $data, $partial = false)
    {
        if (empty($data[self::FIELD_EMAIL])) {
            $this->addDetailedError(
                Mage::helper('customer')->__('The email address cannot be empty.'),
                self::FIELD_EMAIL
            );
        }

        if (!Zend_Validate::is($data[self::FIELD_EMAIL], 'EmailAddress')) {
            $this->addDetailedError(
                Mage::helper('customer')->__('Invalid email address "%s".', $data[self::FIELD_EMAIL]),
                self::FIELD_EMAIL
            );
        }

        return count($this->getDetailedErrors()) === 0;
    }
}
