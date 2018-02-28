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
     */
    const CUSTOM_HANDLES = array(
        array(
            'path'   => '/customer/account/login',
            'handle' => 'shopgate_cloudapi_customer_account_login'
        )
    );

    /**
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function execute(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('shopgate_cloudapi/request')->isShopgateRequest()) {
            return $this;
        }

        /** @var Mage_Core_Model_Layout $layout */
        $layout = $observer->getEvent()->getLayout();
        $layout->getUpdate()->addHandle('shopgate_cloudapi_default');
        $this->addCustomHandle($layout);

        return $this;
    }

    /**
     * @param Mage_Core_Model_Layout $layout
     */
    private function addCustomHandle($layout)
    {
        /** @var Mage_Core_Controller_Request_Http $request */
        $request = Mage::app()->getRequest();
        $path    = sprintf(
            "/%s/%s/%s",
            $request->getModuleName(),
            $request->getControllerName(),
            $request->getActionName()
        );

        foreach (self::CUSTOM_HANDLES as $handle) {
            if ($handle['path'] === $path) {
                $layout->getUpdate()->addHandle($handle['handle']);
            }
        }
    }
}
