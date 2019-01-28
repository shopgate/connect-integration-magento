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
     * Shopgate customer account create route
     */
    const SHOPGATE_CUSTOMER_ACCOUNT_CREATE_ROUTE = 'shopgate-customer/customer_account/create';

    /**
     * Register libraries
     */
    public function __construct()
    {
        Mage::getSingleton('shopgate_cloudapi/autoloader')->createAndRegister();
    }

    /**
     * Checks if the order is received from Shopgate API call.
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function execute(Varien_Event_Observer $observer)
    {
        if (!$this->getRequestHelper()->isShopgateRequest()) {
            return $this;
        }

        /** @var Mage_Customer_Model_Customer $customer */
        $customer = $observer->getEvent()->getData('customer');

        try {
            $code = $this->createAuthorizationCode($customer);
            Mage::helper('shopgate_cloudapi/frontend_quote')->createNewCustomerQuote($customer, $customer->getStore());
        } catch (Exception $exception) {
            Mage::logException($exception);
            $code = '0';
        }

        /** @var Mage_Customer_AccountController $accountController */
        $accountController = $observer->getEvent()->getData('account_controller');
        $response          = $accountController->getResponse();
        $params            = $this->getUtmParams();
        $params['token']   = $code;
        $params['_store']  = $customer->getStore();

        if ($this->getRequestHelper()->isShopgateCheckout()) {
            /** @var Mage_Customer_Model_Session $session */
            $session = Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customer);
            $session->renewSession();
        }

        $redirectUrl = Mage::getUrl(self::SHOPGATE_CUSTOMER_ACCOUNT_CREATE_ROUTE, $params);
        $response->setRedirect($redirectUrl);
        $response->sendResponse();
        exit();
    }

    /**
     * @return Shopgate_Cloudapi_Helper_Request
     */
    private function getRequestHelper()
    {
        return Mage::helper('shopgate_cloudapi/request');
    }

    /**
     * Create authorization code to pass to the pipeline
     *
     * @param Mage_Customer_Model_Customer $customer
     *
     * @return string
     * @throws Mage_Core_Exception
     */
    private function createAuthorizationCode(Mage_Customer_Model_Customer $customer)
    {
        $server = Mage::getModel('shopgate_cloudapi/oAuth2_server')->initialize($customer->getStore());
        /** @var Shopgate_Cloudapi_Model_OAuth2_Db_Pdo $storage */
        $storage = $server->getStorage('authorization_code');
        /** @var \OAuth2\ResponseType\AuthorizationCode $responseType */
        $responseType = $server->getResponseType('code');

        if (!$responseType instanceof \OAuth2\ResponseType\AuthorizationCode) {
            return '0';
        }

        /** @noinspection PhpParamsInspection */
        return $responseType->createAuthorizationCode(
            $storage->getClientId(), $customer->getData('entity_id'), 'customer/register'
        );
    }

    /**
     * Retrieves Google tracking parameters if there are any
     *
     * @return array
     */
    private function getUtmParams()
    {
        $params  = array();
        $utmKeys = array('utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content');
        foreach ($utmKeys as $key) {
            $params[$key] = Mage::app()->getRequest()->getParam($key);
        }

        return array_filter($params);
    }
}
