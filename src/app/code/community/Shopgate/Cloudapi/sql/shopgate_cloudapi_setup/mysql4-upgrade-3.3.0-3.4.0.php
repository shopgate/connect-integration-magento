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

/** @var Shopgate_Cloudapi_Model_Resource_Setup $installer */
$installer = $this;
$installer->startSetup();

try {
    /**
     * Add REST ACL attributes
     */
    $installer->getAclAttributeHelper()->addAclAttributes(Mage_Api2_Model_Auth_User_Customer::USER_TYPE);
    $installer->getAclAttributeHelper()->addAclAttributes(Mage_Api2_Model_Auth_User_Admin::USER_TYPE);
    /**
     * Add REST admin role rules
     */
    $role = $installer->getAclRoleHelper()->getAdminRole();
    $installer->getAclRuleHelper()->addAclRules($role->getId());
} catch (Exception $e) {
    Mage::logException($e);
}
$installer->endSetup();
