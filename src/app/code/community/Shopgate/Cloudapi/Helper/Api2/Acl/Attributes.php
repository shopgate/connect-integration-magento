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

class Shopgate_Cloudapi_Helper_Api2_Acl_Attributes extends Shopgate_Cloudapi_Helper_Api2_Acl
{
    /**
     * Enables all Shopgate Cloud REST Attributes for all endpoints
     * that appear in that menu.
     *
     * @param string $userType - system types like customer / admin / guest
     *
     * @see Mage_Api2_Model_Auth_User::getUserTypes()
     *
     * @throws Exception
     */
    public function addOurAclAttributes($userType)
    {
        $resourceList = $this->getResourceAttributes($userType);
        if ($resourceList) {
            $this->deleteOurAclAttributes($userType);
        }

        foreach ($resourceList as $resource => $operations) {
            foreach ($operations as $operation => $attributes) {
                /** @var Mage_Api2_Model_Acl_Filter_Attribute $attribute */
                Mage::getModel('api2/acl_filter_attribute')
                    ->setUserType($userType)
                    ->setOperation($operation)
                    ->setAllowedAttributes(implode(',', $attributes))
                    ->setResourceId($resource)
                    ->save();
            }
        }
    }

    /**
     * Deletes all existing Shopgate REST Attribute selections
     *
     * @param string $userType - customer/admin/guest
     *
     * @throws Exception
     */
    public function deleteOurAclAttributes($userType)
    {
        $collection = Mage::getResourceModel('api2/acl_filter_attribute_collection')
                          ->addFilterByUserType($userType)
                          ->addFieldToFilter('resource_id', array('like' => self::SG_RESOURCE_NAMESPACE . '_%'));
        foreach ($collection as $filter) {
            $filter->delete();
        }
    }

    /**
     * Get all possible REST operations and corresponding attributes,
     * this data is gathered from our api2.xml and some other conditions.
     *
     * @param string $userType - customer/admin/guest
     *
     * @return array
     * @throws Exception
     */
    private function getResourceAttributes($userType)
    {
        $aclFilter   = Mage::getModel('api2/acl_filter_attribute');
        $model       = $aclFilter->getPermissionModel();
        $permissions = $model->setFilterValue($userType)
                             ->getResourcesPermissions();
        if (isset($permissions['all'])) {
            return array();
        }
        $resourceList = array();
        foreach ($permissions as $resource => $permission) {
            if (strpos($resource, self::SG_RESOURCE_NAMESPACE . '_') !== false) {
                $resourceList[$resource] = $this->extractOperations($permission['operations']);
            }
        }

        return $resourceList;
    }

    /**
     * Helps parse the operations data array
     *
     * @param array $operations
     *
     * @return array
     */
    private function extractOperations(array $operations)
    {
        $list = array();
        foreach ($operations as $operation => $attributes) {
            $list[$operation] = array_keys($attributes['attributes']);
        }

        return $list;
    }
}
