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
 * This observer is responsible for populating Shopgate REST Attributes
 * when one is created and is set to be visible on frontend
 */
class Shopgate_Cloudapi_Model_Observers_AclAttributeManipulation
{
    /**
     * After customer address and customer custom attributes are saved, they get either
     * 1) Removed from the REST attribute list if Visible on Frontend is disabled
     * 2) Added to the REST attribute list if Visible on Frontend is enabled
     *
     * @param Varien_Event_Observer $observer
     *
     * @return Shopgate_Cloudapi_Model_Observers_AclAttributeManipulation
     * @throws Exception
     */
    public function execute(Varien_Event_Observer $observer)
    {
        /** @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
        $attribute = $observer->getEvent()->getData('attribute');
        $isDeleted = $this->isDeleted($observer->getEvent()->getName());
        if ($isDeleted
            || ($attribute->getData('is_user_defined') && $attribute->dataHasChangedFor('is_visible'))
        ) {
            $callName = $this->getCallType($observer->getEvent()->getName());
            /** @var $collection Mage_Api2_Model_Resource_Acl_Filter_Attribute_Collection */
            $collection = Mage::getResourceModel('api2/acl_filter_attribute_collection')
                              ->addFieldToFilter('resource_id', array('eq' => 'shopgate_cloudapi_v2_' . $callName));
            /** @var $aclFilter Mage_Api2_Model_Acl_Filter_Attribute */
            foreach ($collection as $aclFilter) {
                $allowedAttributes = explode(',', $aclFilter->getAllowedAttributes());
                /**
                 * If not visible or deleted remove the attribute from the allowable list, else add it.
                 */
                $allowedAttributes = !$attribute->getIsVisible() || $isDeleted
                    ? array_diff($allowedAttributes, array($attribute->getAttributeCode()))
                    : array_unique(array_merge($allowedAttributes, array($attribute->getAttributeCode())));
                $aclFilter->setAllowedAttributes(implode(',', $allowedAttributes))->save();
            }
        }

        return $this;
    }

    /**
     * Checks if the current fired event is an attribute removal (delete)
     *
     * @param string $eventName
     *
     * @return bool
     */
    private function isDeleted($eventName)
    {
        return in_array(
            $eventName,
            array('enterprise_customer_address_attribute_delete', 'enterprise_customer_attribute_delete')
        );
    }

    /**
     * Using the event name we derive the api2 attribute ACL route
     *
     * @param string $eventName
     *
     * @return string
     */
    private function getCallType($eventName)
    {
        return str_replace(
            array('customer', 'address', 'enterprise_', '_attribute_delete', '_attribute_save'),
            array('customers', 'addresses', '', '', ''),
            $eventName
        );
    }
}
