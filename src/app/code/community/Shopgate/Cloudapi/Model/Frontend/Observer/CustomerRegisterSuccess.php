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

class Shopgate_Cloudapi_Model_Frontend_Observer_CustomerRegisterSuccess
{
    /**
     * Checks if the order is received from Shopgate API call.
     *
     * @param Varien_Event_Observer $observer
     */
    public function execute(Varien_Event_Observer $observer)
    {
        /* @todo-sg - Check is shopgate App register */
        /** @var Mage_Customer_Model_Customer $customer */
        $customer = $observer->getEvent()->getData('customer');

        /** @var Mage_Customer_AccountController $accountController */
        $accountController = $observer->getEvent()->getAccountController();
        $response          = $accountController->getResponse();
        /* @todo-sg - create token and add to params */
        $params      = array('token' => $customer->getId());
        $redirectUrl = Mage::getUrl('shopgate-customer/customer_account/create', $params);
        $response->setRedirect($redirectUrl);
        $response->sendResponse();
        $response->sendHeadersAndExit();
    }
}
