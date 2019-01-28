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

class Shopgate_Cloudapi_Helper_Api2_Acl_Rules extends Shopgate_Cloudapi_Helper_Api2_Acl
{
    /**
     * Allow all Shopgate endpoints for Customer & Guest users
     *
     * @param int|null $roleId - ID handles Admin role type, null handles customer|guest type
     *
     * @throws Exception
     */
    public function addAclRules($roleId = null)
    {
        $rules = $this->getAclRules($roleId);
        if ($rules->getSize() > 0) {
            $this->deleteAclRules($roleId);
        }

        foreach ($this->getResources() as $resource) {
            $roleId ? $this->createRuleByRoleId($resource, $roleId) : $this->addSystemRules($resource);
        }
    }

    /**
     * Removes Shopgate ACL rules
     *
     * @param int|null $roleId
     *
     * @throws Exception
     */
    public function deleteAclRules($roleId = null)
    {
        $roleId ? $this->deleteRulesByRoleId($roleId) : $this->deleteSystemRules();
    }

    /**
     * Retrieves existing rule collection
     *
     * @param int|null $roleId
     *
     * @return Mage_Api2_Model_Resource_Acl_Global_Rule_Collection
     */
    public function getAclRules($roleId = null)
    {
        /** @var Mage_Api2_Model_Resource_Acl_Global_Rule_Collection $collection */
        $collection = Mage::getModel('api2/acl_global_rule')
                          ->getCollection()
                          ->addFieldToFilter('resource_id', array('like' => self::RESOURCE . '%'));
        if ($roleId) {
            $collection->addFilterByRoleId((integer) $roleId);
        } else {
            $collection->addFieldToFilter(
                'role_id',
                array('in' => Mage_Api2_Model_Acl_Global_Role::getSystemRoles())
            );
        }

        return $collection;
    }

    /**
     * Adds all endpoint role allowances to system roles.
     * System roles being customer and guest.
     *
     * @param string $resource
     *
     * @throws Exception
     */
    private function addSystemRules($resource)
    {
        foreach (Mage_Api2_Model_Acl_Global_Role::getSystemRoles() as $systemRoleId) {
            $this->createRuleByRoleId($resource, $systemRoleId);
        }
    }

    /**
     * Delete system rules pertaining to this resource
     *
     * @throws Exception
     */
    private function deleteSystemRules()
    {
        foreach (Mage_Api2_Model_Acl_Global_Role::getSystemRoles() as $systemRoleId) {
            $this->deleteRulesByRoleId($systemRoleId);
        }
    }

    /**
     * Deletes all SG Rules for the Role id provided
     *
     * @param int|null $roleId
     *
     * @throws Exception
     */
    private function deleteRulesByRoleId($roleId)
    {
        $collection = $this->getAclRules($roleId);
        foreach ($collection as $item) {
            $item->delete();
        }
    }

    /**
     * @param string $resource
     * @param int    $roleId
     *
     * @throws Exception
     */
    private function createRuleByRoleId($resource, $roleId)
    {
        /** @var Mage_Api2_Model_Acl_Global_Role $api2AclRoleModel */
        $api2AclRoleModel = Mage::getModel('api2/acl_global_role');
        $api2AclRoleModel->setId($roleId);
        $resourceUserPrivileges =
            $this->getApi2Config()->getResourceUserPrivileges($resource, $api2AclRoleModel->getConfigNodeName());
        foreach ($resourceUserPrivileges as $privilegeKey => $privilegeValue) {
            if ($privilegeValue) {
                $this->createRule($roleId, $resource, $privilegeKey);
            }
        }
    }

    /**
     * @param string $namespace
     *
     * @return bool
     */
    private function isCorrectNamespace($namespace)
    {
        return 0 === strpos($namespace, self::RESOURCE);
    }

    /**
     * @return Mage_Api2_Model_Config
     */
    private function getApi2Config()
    {
        return Mage::getModel('api2/config');
    }

    /**
     * @param int    $roleId
     * @param int    $resource
     * @param string $privilege
     *
     * @throws Exception
     */
    private function createRule($roleId, $resource, $privilege)
    {
        /** @var Mage_Api2_Model_Acl_Global_Rule $rule */
        $rule = Mage::getModel('api2/acl_global_rule');
        $rule->setRoleId($roleId)
             ->setResourceId($resource)
             ->setPrivilege($privilege)
             ->save();
    }

    /**
     * Get Shopgate endpoint resources
     *
     * @return string[]
     */
    private function getResources()
    {
        $list = array();
        foreach ($this->getApi2Config()->getResourcesTypes() as $resource) {
            if ($this->isCorrectNamespace($resource)) {
                $list[] = $resource;
            }
        }

        return $list;
    }
}
