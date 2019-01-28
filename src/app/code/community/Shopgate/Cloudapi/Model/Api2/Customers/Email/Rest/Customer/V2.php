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

class Shopgate_Cloudapi_Model_Api2_Customers_Email_Rest_Customer_V2
    extends Shopgate_Cloudapi_Model_Api2_Customers_Email_Rest
{
    /** @noinspection PhpHierarchyChecksInspection */
    /**
     * Update email address
     *
     * @param array $data - ('email' => 'example@example.com')
     *
     * @throws Mage_Api2_Exception
     * @throws Exception
     */
    public function _update(array $data)
    {
        $customer  = $this->loadCustomerById((int)$this->getRequest()->getParam('customer_id'));
        $oldEmail  = $customer->getData('email');
        $validator = $this->getValidator()->setCustomer($customer);
        if (!$validator->isValidData($data)) {
            $this->_render($this->setDetailedErrors($validator)->sendInvalidationResponse());

            return;
        }

        try {
            Mage::getModel('customer/customer_api')->update($this->getApiUser()->getUserId(), $data);
        } catch (Mage_Core_Exception $e) {
            $this->_critical($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        } catch (Exception $e) {
            $this->_critical(self::RESOURCE_INTERNAL_ERROR);
        }

        if (method_exists($customer, 'sendChangedPasswordOrEmail')) {
            $customer->setData('old_email', $oldEmail);
            $customer->sendChangedPasswordOrEmail();
        }
    }

    /**
     * @return Shopgate_Cloudapi_Model_Api2_Customers_Email_Validator
     */
    public function getValidator()
    {
        return Mage::getModel('shopgate_cloudapi/api2_customers_email_validator', array('resource' => $this));
    }
}
