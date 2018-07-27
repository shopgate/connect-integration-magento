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
/** @noinspection PhpIncludeInspection */

require_once './abstract.php';

/** @noinspection AutoloadingIssuesInspection */

class Shopgate_Cloudapi_Shell extends Mage_Shell_Abstract
{
    /**
     * Run SG script
     *
     * @throws Exception
     */
    public function run()
    {
        if ($this->getArg('acl')) {
            if ($this->getArg('attributes')) {
                $this->updateAclAttributes();

                return;
            }
            if ($this->getArg('rules')) {
                $this->updateAclRules();

                return;
            }
        }

        $this->usageHelp();
    }

    /**
     * Make all Shopgate Cloud endpoints under
     * REST Roles > Customer to be accessible
     *
     * @throws Exception
     */
    private function updateAclAttributes()
    {
        $helper = $this->getAttrHelper();
        $helper->addAclAttributes(Mage_Api2_Model_Auth_User_Customer::USER_TYPE);
        $helper->addAclAttributes(Mage_Api2_Model_Auth_User_Admin::USER_TYPE);
    }

    /**
     * Deletes all ACL Rules & adds them back
     *
     * @throws Exception
     */
    private function updateAclRules()
    {
        $this->getRuleHelper()->addAclRules();

        $role = $this->getRoleHelper()->getAdminRole();
        if (!$role->getId()) {
            $role = $this->getRoleHelper()->createAdminRole();
        }
        $this->getRuleHelper()->addAclRules($role->getId());
    }

    /**
     * @return Shopgate_Cloudapi_Helper_Api2_Acl_Attributes
     */
    private function getAttrHelper()
    {
        return Mage::helper('shopgate_cloudapi/api2_acl_attributes');
    }

    /**
     * @return Shopgate_Cloudapi_Helper_Api2_Acl_Rules
     */
    private function getRuleHelper()
    {
        return Mage::helper('shopgate_cloudapi/api2_acl_rules');
    }

    /**
     * @return Shopgate_Cloudapi_Helper_Api2_Acl_Roles
     */
    private function getRoleHelper()
    {
        return Mage::helper('shopgate_cloudapi/api2_acl_roles');
    }

    /**
     * @return string
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f shopgate_cloudapi.php -- [options]
  acl attributes    Enable all Shopgate REST attributes (endpoint incoming data)
  acl rules         Enable all Shopgate REST Rules (endpoint access)
  -h                Short alias for help
  help              This help
USAGE;
    }
}

$shell = new Shopgate_Cloudapi_Shell();
$shell->run();
