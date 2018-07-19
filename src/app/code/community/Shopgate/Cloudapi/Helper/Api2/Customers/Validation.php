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

class Shopgate_Cloudapi_Helper_Api2_Customers_Validation extends Mage_Core_Helper_Abstract
{
    private static $translations = array(
        'region'     => 'State/Province',
        'postcode'   => 'Zip/Postal Code',
        'country_id' => 'Country',
        'street'     => 'Street Address'
    );

    /**
     * Retrieves the attribute code based on
     * error message received
     *
     * @param string $errorMessage
     *
     * @return string
     */
    public function errorFieldParser($errorMessage)
    {
        if (strpos($errorMessage, 'required') !== false) {
            return $this->getAttributeFromRequiredError($errorMessage);
        }
        foreach (self::$translations as $attribute => $translation) {
            if (strpos($errorMessage, $translation) !== false) {
                return $attribute;
            }
        }

        return '';
    }

    /**
     * Returns an attribute code based on the "required"
     * error
     *
     * @param string $errorMessage
     *
     * @return string
     */
    private function getAttributeFromRequiredError($errorMessage)
    {
        preg_match('/"([^"]+)"/', $errorMessage, $match);
        $error = isset($match[1]) ? $match[1] : false;
        $value = array_search($error, self::$translations, true);

        return $value !== false ? $value : strtolower(preg_replace('/\s/', '', $error));
    }

    /**
     * Checks if the incoming country code is correct.
     * This helps avoid an early exception in some Mage code.
     *
     * @param array $data - required 'country_id' to be passed
     *
     * @return bool
     */
    public function validateCountry($data)
    {
        return isset($data['country_id']) && strlen($data['country_id']) > 1 && strlen($data['country_id']) < 4;
    }
}
