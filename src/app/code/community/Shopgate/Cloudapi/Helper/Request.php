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

class Shopgate_Cloudapi_Helper_Request extends Mage_Core_Helper_Abstract
{
    /**
     * Parameter indicating a shopgate cloud request via browser
     */
    const KEY_SGCLOUD_INAPP = 'sgcloud_inapp';

    /**
     * Parameter indicating a shopgate cloud is checkout
     */
    const KEY_SGCLOUD_CHECKOUT = 'sgcloud_checkout';

    /**
     * Parameter indicating whether the call is made via API
     */
    const KEY_SGCLOUD_API = 'sgcloud_api';

    /**
     * Parameter indicating a shopgate cloud sgcloud_callback_data
     */
    const KEY_SGCLOUD_CALLBACK_DATA = 'sgcloud_callback_data';

    /**
     * Parameter indicating a shopgate connect guest checkout
     */
    const KEY_SGCONNECT_GUEST_CHECKOUT = 'is_guest_checkout';
    /**
     * Name and value of cookie created for sg cloud requests
     */
    const COOKIE_NAME = 'shopgate';
    const COOKIE_VALUE = '1';

    /**
     * @return bool
     */
    public function isShopgateRequest()
    {
        if ($this->parameterInAppDetected()) {
            $this->setCookie();

            return true;
        }

        return $this->userAgentIsShopgateApp() || $this->cookieIsSet(self::KEY_SGCLOUD_INAPP, self::COOKIE_VALUE);
    }

    /**
     * @return bool
     */
    protected function parameterInAppDetected()
    {
        return Mage::app()->getRequest()->getParam(self::KEY_SGCLOUD_INAPP) === self::COOKIE_VALUE;
    }

    /**
     * Will set the shopgate cookie
     */
    protected function setCookie()
    {
        $data = new Varien_Object();
        $data->setData(self::KEY_SGCLOUD_INAPP, self::COOKIE_VALUE);
        $data->setData(self::KEY_SGCLOUD_CHECKOUT, $this->getParam(self::KEY_SGCLOUD_CHECKOUT));
        $data->setData(self::KEY_SGCLOUD_CALLBACK_DATA, $this->getParam(self::KEY_SGCLOUD_CALLBACK_DATA));

        Mage::getSingleton('core/cookie')->set(self::COOKIE_NAME, json_encode($data->getData()), 0);
    }

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return bool
     */
    public function cookieIsSet($key, $value = false)
    {
        $data = $this->getCookie();

        if (!isset($data[$key])) {
            return false;
        }

        return !$value ? true : $data[$key] === $value;
    }

    /**
     * @return string | false
     */
    public function userAgentIsShopgateApp()
    {
        return strstr(Mage::helper('core/http')->getHttpUserAgent(), 'libshopgate') !== false;
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function cookieGetValue($key)
    {
        $data = $this->getCookie();

        return isset($data[$key]) ? $data[$key] : false;
    }

    /**
     * @return bool
     */
    public function isShopgateCheckout()
    {
        return $this->cookieIsSet(self::KEY_SGCLOUD_CHECKOUT);
    }

    /**
     * @return bool
     */
    public function isShopgateGuestCheckout()
    {
        return $this->getParam(self::KEY_SGCONNECT_GUEST_CHECKOUT);
    }

    /**
     * @return bool
     */
    public function isShopgateApi()
    {
        return true === Mage::registry(self::KEY_SGCLOUD_API);
    }

    /**
     * Sets the current call as a Shopgate API call
     */
    public function setShopgateApi()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        Mage::register(self::KEY_SGCLOUD_API, true, true);
    }

    /**
     * @return mixed
     */
    public function getShopgateCallbackData()
    {
        return $this->cookieIsSet(self::KEY_SGCLOUD_CALLBACK_DATA);
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    protected function getParam($key)
    {
       return Mage::app()->getRequest()->getParam($key);
    }

    /**
     * @return array
     */
    protected function getCookie()
    {
        $data = Mage::getSingleton('core/cookie')->get(self::COOKIE_NAME);

        if ($data !== false) {
            $result = json_decode($data, true);
            if (is_array($result)) {
                return $result;
            }
        }

        return array();
    }
}
