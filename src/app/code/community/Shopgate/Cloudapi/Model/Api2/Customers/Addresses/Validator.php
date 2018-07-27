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

class Shopgate_Cloudapi_Model_Api2_Customers_Addresses_Validator extends Shopgate_Cloudapi_Model_Api2_Validator
{
    /** @var Mage_Customer_Model_Api2_Customer_Address_Validator */
    private $validator;
    /** @var Mage_Api2_Model_Resource */
    protected $resource;

    /**
     * @param array $options
     *
     * @throws Exception
     */
    public function __construct(array $options)
    {
        parent::__construct($options);
        $this->resource = $options['resource'];
    }

    /**
     * A rewrite of the mage on as we need to get attributes as well
     *
     * @param array $data
     *
     * @return bool
     */
    public function isValidDataForCreateAssociationWithCountry(array $data)
    {
        $result = $this->countryValidation($data)
            && $this->getValidator()->isValidDataForCreateAssociationWithCountry($data);
        if (!$result) {
            $this->addRegionValidationErrors();
        }

        return $result;
    }

    /**
     * Checks country and sets a soft error as mage throws
     * a hard error when this happens
     *
     * @param array $data
     *
     * @return bool
     */
    private function countryValidation(array $data)
    {
        $valid = isset($data['country_id']) && strlen($data['country_id']) > 1 && strlen($data['country_id']) < 4;
        if (!$valid) {
            $error = Mage::helper('directory')->__('Invalid country code: %s', $data['country_id']);
            $this->addDetailedError($error, 'country_id');
        }

        return $valid;
    }

    /**
     * @param Mage_Customer_Model_Address $address
     * @param array                       $data
     *
     * @return bool
     */
    public function isValidDataForChangeAssociationWithCountry(Mage_Customer_Model_Address $address, array $data)
    {
        $result = $this->countryValidation($data)
            && $this->getValidator()->isValidDataForChangeAssociationWithCountry($address, $data);
        if (!$result) {
            $this->addRegionValidationErrors();
        }

        return $result;
    }

    /**
     * Handles detailed errors for this class
     */
    private function addRegionValidationErrors()
    {
        foreach ($this->getValidator()->getErrors() as $error) {
            $this->addDetailedError($error, 'region');
        }
    }

    /**
     * @return Mage_Customer_Model_Api2_Customer_Address_Validator
     */
    private function getValidator()
    {
        if (null === $this->validator) {
            $this->validator = Mage::getModel(
                'customer/api2_customer_address_validator',
                array('resource' => $this->resource)
            );
        }

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->validator;
    }
}
