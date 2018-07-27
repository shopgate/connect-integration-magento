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
    const SG_ADMIN_USERNAME   = 'shopgate-rest';
    const SG_ADMIN_FIRST_NAME = 'Shopgate';
    const SG_ADMIN_LAST_NAME  = 'REST Consumer';
    const SG_ADMIN_EMAIL      = 'interfaces@shopgate.com';

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
     * to our REST API with. Also allows calls to
     * all endpoints for this user.
     *
     * @throws Exception
     */
    public function createAdminUserAndAssignRole()
    {
        $role = $this->getAclRoleHelper()->createAdminRole();
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
        $this->getAclRuleHelper()->addAclRules($role->getId());
    }

    /**
     * @return Shopgate_Cloudapi_Helper_Api2_Acl_Rules
     */
    public function getAclRuleHelper()
    {
        return Mage::helper('shopgate_cloudapi/api2_acl_rules');
    }

    /**
     * @return Shopgate_Cloudapi_Helper_Api2_Acl_Roles
     */
    public function getAclRoleHelper()
    {
        return Mage::helper('shopgate_cloudapi/api2_acl_roles');
    }

    /**
     * @return Shopgate_Cloudapi_Helper_Api2_Acl_Attributes
     */
    public function getAclAttributeHelper()
    {
        return Mage::helper('shopgate_cloudapi/api2_acl_attributes');
    }
}
