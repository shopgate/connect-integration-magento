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
class Shopgate_Cloudapi_Block_Customer_Account extends Mage_Adminhtml_Block_Template
{
    /**
     * Checkout onepage route
     */
    const CHECKOUT_ONEPAGE_ROUTE = 'checkout/onepage';

    /**
     * @return string
     * @throws Exception
     */
    public function getToken()
    {
        return $this->getRequest()->getParam('token', '');
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getShopgateCallbackData()
    {
        if ($this->getRequestHelper()->getShopgateCallbackData()) {
            return $this->getRequestHelper()->cookieGetValue(
                Shopgate_Cloudapi_Helper_Request::KEY_SGCLOUD_CALLBACK_DATA
            );
        } else {
            return '';
        }
    }

    /**
     * @return bool
     */
    public function isShopgateCheckout()
    {
        return $this->getRequestHelper()->isShopgateCheckout();
    }

    /**
     * @return string
     */
    public function getCheckoutUrl()
    {
        return $this->getUrl(self::CHECKOUT_ONEPAGE_ROUTE, array('_secure' => true));
    }

    /**
     * @return Shopgate_Cloudapi_Helper_Request
     */
    protected function getRequestHelper()
    {
        return Mage::helper('shopgate_cloudapi/request');
    }
}
