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

class Shopgate_Cloudapi_Helper_Api2_Acl_Roles extends Shopgate_Cloudapi_Helper_Api2_Acl
{
    const ROLE_NAME = 'Shopgate REST';

    /**
     * @return Mage_Api2_Model_Acl_Global_Role
     */
    public function createAdminRole()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        /** @var Mage_Api2_Model_Acl_Global_Role $role */
        return Mage::getModel('api2/acl_global_role')
                   ->setRoleName(self::ROLE_NAME)
                   ->setResources(array('all'))
                   ->save();
    }

    /**
     * @return Mage_Api2_Model_Acl_Global_Role
     */
    public function getAdminRole()
    {
        return Mage::getModel('api2/acl_global_role')
                   ->getCollection()
                   ->addFieldToFilter('role_name', self::ROLE_NAME)
                   ->getFirstItem();
    }
}
