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

class Shopgate_Cloudapi_Model_Frontend_Observer_Layout
{
    /**
     * Define custom handles
     *
     * @var array[] $customHandles
     */
    private $customHandles;

    /**
     * Init custom handles
     */
    public function __construct()
    {
        $this->customHandles = array(
            array(
                'path'     => 'customer/account/login',
                'handle'   => 'shopgate_cloudapi_customer_account_login',
                'isActive' => true
            ),
            array(
                'path'     => 'checkout/onepage/index',
                'handle'   => 'shopgate_cloudapi_checkout_onepage_index',
                'isActive' => $this->getRequestHelper()->isShopgateGuestCheckout()
            )
        );
    }

    /**
     * @return Shopgate_Cloudapi_Helper_Request
     */
    protected function getRequestHelper()
    {
        return Mage::helper('shopgate_cloudapi/request');
    }

    /**
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function execute(Varien_Event_Observer $observer)
    {
        if (!$this->getRequestHelper()->isShopgateRequest()) {
            return $this;
        }

        /** @var Mage_Core_Model_Layout $layout */
        /** @noinspection PhpUndefinedMethodInspection */
        $layout = $observer->getEvent()->getLayout();
        $layout->getUpdate()->addHandle('shopgate_cloudapi_default');
        $this->addCustomHandles($layout);

        return $this;
    }

    /**
     * @param Mage_Core_Model_Layout $layout
     */
    private function addCustomHandles(Mage_Core_Model_Layout $layout)
    {
        /** @var Mage_Core_Controller_Request_Http $request */
        $request = Mage::app()->getRequest();
        $path    = sprintf(
            '%s/%s/%s',
            $request->getModuleName(),
            $request->getControllerName(),
            $request->getActionName()
        );

        foreach ($this->customHandles as $handle) {
            if ($handle['path'] === $path && $handle['isActive']) {
                $layout->getUpdate()->addHandle($handle['handle']);
            }
        }
    }
}
