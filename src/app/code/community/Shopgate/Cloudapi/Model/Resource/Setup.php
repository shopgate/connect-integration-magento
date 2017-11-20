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

class Shopgate_Cloudapi_Model_Resource_Setup extends Mage_Core_Model_Resource_Setup
{
    const SG_RESOURCE_NAMESPACE     = 'shopgate_cloudapi';
    const SG_ADMIN_USERNAME         = 'shopgate-rest';
    const SG_ADMIN_FIRST_NAME       = 'Shopgate';
    const SG_ADMIN_LAST_NAME        = 'REST Consumer';
    const SG_ADMIN_EMAIL            = 'interfaces@shopgate.com';

    /**
     * Loads our library & OAuth2
     *
     * @inheritdoc
     */
    public function __construct($resourceName)
    {
        Mage::getSingleton('shopgate_cloudapi/autoloader')->createAndRegister();
        parent::__construct($resourceName);
    }

    /**
     * Creates a Shopgate Admin role to make calls
     * to our REST API with.
     */
    public function createAdminUserAndAssignRole()
    {
        //todo-sg: adjust to allow shopgate specific resources instead, to keep it extra secure
        /** @noinspection PhpUndefinedMethodInspection */
        /** @var Mage_Api2_Model_Acl_Global_Role $role */
        $role = Mage::getModel('api2/acl_global_role')
            ->setRoleName('Shopgate REST')
            ->setResources(array('all'))
            ->save();

        $pass = Mage::helper('core')->getRandomString(8);
        $user = Mage::getModel('admin/user')
            ->setData(
                array(
                    'username'  => self::SG_ADMIN_USERNAME,
                    'firstname' => self::SG_ADMIN_FIRST_NAME,
                    'lastname'  => self::SG_ADMIN_LAST_NAME,
                    'email'     => self::SG_ADMIN_EMAIL,
                    'password'  => $pass,
                    'is_active' => 1
                )
            );

        /** @noinspection PhpUndefinedMethodInspection */
        $user->save();
        $role->getResource()->saveAdminToRoleRelation($user->getId(), $role->getId());

        $rule = Mage::getModel('api2/acl_global_rule');
        $rule->setRoleId($role->getId())
            ->setResourceId('all')
            ->setPrivilege('create')
            ->save();

    }

    /**
     * append rules to resources
     */
    public function appendRules()
    {
        foreach ($this->_getApi2Config()->getResourcesTypes() as $resource) {
            if ($this->_validateNameSpace($resource)) {
                $this->_addSystemRules($resource);
            }
        };
    }

    /**
     * @param string $resource
     */
    protected function _addSystemRules($resource)
    {
        foreach (Mage_Api2_Model_Acl_Global_Role::getSystemRoles() as $systemRoleId) {
            $this->_createRuleByPrivilege($resource, $systemRoleId);
        }
    }

    /**
     * @param string $resource
     * @param int $systemRoleId
     */
    protected function _createRuleByPrivilege($resource, $systemRoleId)
    {
        /** @var Mage_Api2_Model_Acl_Global_Role $api2AclRoleModel */
        $api2AclRoleModel = Mage::getModel('api2/acl_global_role');
        $api2AclRoleModel->setId($systemRoleId);
        $resourceUserPrivileges = $this->_getApi2Config()->getResourceUserPrivileges($resource, $api2AclRoleModel->getConfigNodeName());
        foreach ($resourceUserPrivileges as $privilegeKey => $privilegeValue) {
            if ($privilegeValue) {
                $this->_createRule($systemRoleId, $resource, $privilegeKey);
            }
        }
    }

    /**
     * @param string $nameSpace
     * @return bool
     */
    protected function _validateNameSpace($nameSpace)
    {
        return 0 === strpos($nameSpace, self::SG_RESOURCE_NAMESPACE);
    }

    /**
     * @return Mage_Api2_Model_Config
     */
    protected function _getApi2Config()
    {
        return Mage::getModel('api2/config');
    }

    /**
     * @param int $roleId
     * @param int $resource
     * @param string $privilege
     */
    protected function _createRule($roleId, $resource, $privilege)
    {
        /** @var Mage_Api2_Model_Acl_Global_Rule $rule */
        $rule = Mage::getModel('api2/acl_global_rule');
        $rule->setRoleId($roleId)
            ->setResourceId($resource)
            ->setPrivilege($privilege)
            ->save();
    }
}
