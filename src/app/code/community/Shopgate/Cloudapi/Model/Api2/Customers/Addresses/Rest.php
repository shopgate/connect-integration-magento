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

class Shopgate_Cloudapi_Model_Api2_Customers_Addresses_Rest extends Shopgate_Cloudapi_Model_Api2_Resource
{
    /**
     * Create customer address
     *
     * @param array $data
     *
     * @throws Mage_Api2_Exception
     * @throws Exception
     * @return array
     */
    protected function _create(array $data)
    {
        /* @var $customer Mage_Customer_Model_Customer */
        $customer  = $this->_loadCustomerById($this->getRequest()->getParam('customer_id'));
        $validator = $this->_getValidator();
        $helper    = $this->getHelper();

        $data        = $validator->filter($data);
        $checkData   = $validator->isValidData($data);
        $checkRegion = $helper->validateCountry($data) && $validator->isValidDataForCreateAssociationWithCountry($data);
        if (!$checkData || !$checkRegion) {
            foreach ($validator->getErrors() as $error) {
                $this->_errorMessage(
                    $error,
                    Mage_Api2_Model_Server::HTTP_BAD_REQUEST,
                    array('path' => $helper->errorFieldParser($error))
                );
            }

            return $this->sendInvalidationResponse();
        }

        if (isset($data['region'], $data['country_id'])) {
            $data['region'] = $this->_getRegionIdByNameOrCode($data['region'], $data['country_id']);
        }

        /* @var $address Mage_Customer_Model_Address */
        $address = Mage::getModel('customer/address');
        $address->setData($data);
        $address->setCustomer($customer);

        try {
            $address->save();
        } catch (Mage_Core_Exception $e) {
            $this->_error($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        } catch (Exception $e) {
            $this->_critical(self::RESOURCE_INTERNAL_ERROR);
        }

        return array('addressId' => $address->getId());
    }

    /**
     * Retrieve information about specified customer address
     *
     * @throws Mage_Api2_Exception
     * @throws Exception
     * @return array
     */
    protected function _retrieve()
    {
        /* @var $address Mage_Customer_Model_Address */
        $address               = $this->_loadCustomerAddressById($this->getRequest()->getParam('id'));
        $addressData           = $address->getData();
        $addressData['street'] = $address->getStreet();

        return $addressData;
    }

    /**
     * Get customer addresses list
     *
     * @return array
     * @throws Mage_Api2_Exception
     */
    protected function _retrieveCollection()
    {
        $data = array();
        /* @var $address Mage_Customer_Model_Address */
        foreach ($this->_getCollectionForRetrieve() as $address) {
            $addressData                     = $address->getData();
            $addressData['street']           = $address->getStreet();
            $addressData['customAttributes'] = $this->getCustomAttributes($address);
            $addressData                     = array_diff_key($addressData, $addressData['customAttributes']);
            $data[]                          = array_merge($addressData, $this->_getDefaultAddressesInfo($address));
        }

        return $data;
    }

    /**
     * Retrieve collection instances
     *
     * @return Mage_Customer_Model_Resource_Address_Collection
     * @throws Mage_Api2_Exception
     * @throws Exception
     */
    protected function _getCollectionForRetrieve()
    {
        /* @var $customer Mage_Customer_Model_Customer */
        $customer = $this->_loadCustomerById($this->getRequest()->getParam('customer_id'));

        /* @var $collection Mage_Customer_Model_Resource_Address_Collection */
        $collection = $customer->getAddressesCollection();

        $this->_applyCollectionModifiers($collection);

        return $collection;
    }

    /**
     * Get array with default addresses information if possible
     *
     * @param Mage_Customer_Model_Address $address
     *
     * @return array
     */
    protected function _getDefaultAddressesInfo(Mage_Customer_Model_Address $address)
    {
        return array(
            'is_default_billing'  => (int) $this->_isDefaultBillingAddress($address),
            'is_default_shipping' => (int) $this->_isDefaultShippingAddress($address)
        );
    }

    /**
     * Update specified address
     *
     * @param array $data
     *
     * @throws Mage_Api2_Exception
     * @throws Exception
     */
    protected function _update(array $data)
    {
        /* @var $address Mage_Customer_Model_Address */
        $address   = $this->_loadCustomerAddressById($this->getRequest()->getParam('id'));
        $validator = $this->_getValidator();

        $data = $validator->filter($data);
        if (!$validator->isValidData($data, true)
            || !$validator->isValidDataForChangeAssociationWithCountry($address, $data)
        ) {
            foreach ($validator->getErrors() as $error) {
                $this->_error($error, Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
            }
            $this->_critical(self::RESOURCE_DATA_PRE_VALIDATION_ERROR);
        }
        if (isset($data['region'])) {
            $data['region']    = $this->_getRegionIdByNameOrCode(
                $data['region'],
                isset($data['country_id']) ? $data['country_id'] : $address->getCountryId()
            );
            $data['region_id'] = null; // to avoid overwrite region during update in address model _beforeSave()
        }
        $address->addData($data);

        try {
            $address->save();
        } catch (Mage_Core_Exception $e) {
            $this->_error($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        } catch (Exception $e) {
            $this->_critical(self::RESOURCE_INTERNAL_ERROR);
        }
    }

    /**
     * Delete customer address
     *
     * @throws Mage_Api2_Exception
     * @throws Exception
     */
    protected function _delete()
    {
        /* @var $address Mage_Customer_Model_Address */
        $address = $this->_loadCustomerAddressById($this->getRequest()->getParam('id'));

        if ($this->_isDefaultBillingAddress($address) || $this->_isDefaultShippingAddress($address)) {
            $this->_critical(
                'Address is default for customer so is not allowed to be deleted',
                Mage_Api2_Model_Server::HTTP_BAD_REQUEST
            );
        }
        try {
            $address->delete();
        } catch (Mage_Core_Exception $e) {
            $this->_critical($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        } catch (Exception $e) {
            $this->_critical(self::RESOURCE_INTERNAL_ERROR);
        }
    }

    /**
     * Resource specific method to retrieve attributes' codes. May be overriden in child.
     *
     * @return array
     */
    protected function _getResourceAttributes()
    {
        return $this->getEavAttributes(Mage_Api2_Model_Auth_User_Admin::USER_TYPE !== $this->getUserType());
    }

    /**
     * Get customer address resource validator instance
     *
     * @return Mage_Customer_Model_Api2_Customer_Address_Validator
     */
    protected function _getValidator()
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return Mage::getModel('customer/api2_customer_address_validator', array('resource' => $this));
    }

    /**
     * Is specified address a default billing address?
     *
     * @param Mage_Customer_Model_Address $address
     *
     * @return bool
     */
    protected function _isDefaultBillingAddress(Mage_Customer_Model_Address $address)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $address->getCustomer()->getDefaultBilling() === $address->getId();
    }

    /**
     * Is specified address a default shipping address?
     *
     * @param Mage_Customer_Model_Address $address
     *
     * @return bool
     */
    protected function _isDefaultShippingAddress(Mage_Customer_Model_Address $address)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $address->getCustomer()->getDefaultShipping() === $address->getId();
    }

    /**
     * Get region id by name or code
     * If id is not found then return passed $region
     *
     * @param string $region
     * @param string $countryId
     *
     * @return int|string
     */
    protected function _getRegionIdByNameOrCode($region, $countryId)
    {
        /** @var $collection Mage_Directory_Model_Resource_Region_Collection */
        $collection = Mage::getResourceModel('directory/region_collection');

        $collection->getSelect()
                   ->reset()// to avoid locale usage
                   ->from(array('main_table' => $collection->getMainTable()), 'region_id');

        $collection->addCountryFilter($countryId)
                   ->addFieldToFilter(array('default_name', 'code'), array($region, $region));

        $id = $collection->getResource()->getReadConnection()->fetchOne($collection->getSelect());

        return $id ? (int) $id : $region;
    }

    /**
     * Load customer address by id
     *
     * @param int $id
     *
     * @return Mage_Customer_Model_Address
     * @throws Mage_Api2_Exception
     */
    protected function _loadCustomerAddressById($id)
    {
        /* @var $address Mage_Customer_Model_Address */
        $address = Mage::getModel('customer/address')->load($id);

        if (!$address->getId()) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        $address->addData($this->_getDefaultAddressesInfo($address));

        return $address;
    }

    /**
     * Load customer by id
     *
     * @param int $id
     *
     * @throws Mage_Api2_Exception
     * @return Mage_Customer_Model_Customer
     */
    protected function _loadCustomerById($id)
    {
        /* @var $customer Mage_Customer_Model_Customer */
        $customer = Mage::getModel('customer/customer')->load($id);
        if (!$customer->getId()) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }

        return $customer;
    }

    /**
     * Retrieve custom attribute key=>value pairs
     *
     * @param Mage_Customer_Model_Address $address
     *
     * @return array
     */
    private function getCustomAttributes(Mage_Customer_Model_Address $address)
    {
        $attributes = Mage::getResourceModel('eav/entity_attribute_collection')
                          ->setEntityTypeFilter($address->getEntityTypeId())
                          ->addFilter('is_user_defined', 1);
        $list       = array();
        /** @var Mage_Eav_Model_Attribute $attribute */
        foreach ($attributes as $attribute) {
            $code        = $attribute->getAttributeCode();
            $list[$code] = $address->getData($code);
        }

        return $list;
    }

    /**
     * This will take away the power from our api2.xml
     * and use the customer address api2.xml for filtering,
     * validation and possible other side effects.
     *
     * @return string
     */
    public function getResourceType()
    {
        return 'customer_address';
    }

    /**
     * Bypasses the exception state and passes down invalidation errors
     *
     * @throws Zend_Controller_Response_Exception
     * @throws Exception
     */
    private function sendInvalidationResponse()
    {
        $this->getResponse()->setHttpResponseCode(400);

        return $this->getResponse()->getMessages();
    }

    /**
     * @return Shopgate_Cloudapi_Helper_Api2_Customers_Validation
     */
    private function getHelper()
    {
        return Mage::helper('shopgate_cloudapi/api2_customers_validation');
    }
}
