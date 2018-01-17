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
     * Parameter indicating a shopgate cloud request
     */
    const KEY_SGCLOUD_INAPP = 'sgcloud_inapp';

    /**
     * Parameter indicating a shopgate cloud is checkout
     */
    const KEY_SGCLOUD_IS_CHECKOUT = 'sgcloud_is_checkout';

    /**
     * Parameter indicating a shopgate cloud sgcloud_callback_data
     */
    const KEY_SGCLOUD_CALLBACK_DATA = 'sgcloud_callback_data';

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

        return $this->cookieIsSet(self::KEY_SGCLOUD_INAPP,  self::COOKIE_VALUE);
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
        $data->setData(self::KEY_SGCLOUD_IS_CHECKOUT, $this->getParam(self::KEY_SGCLOUD_IS_CHECKOUT));
        $data->setData(self::KEY_SGCLOUD_CALLBACK_DATA, $this->getParam(self::KEY_SGCLOUD_CALLBACK_DATA));

        Mage::getSingleton('core/cookie')->set(self::COOKIE_NAME, json_encode($data->getData()), 0);
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @return bool
     */
    public function cookieIsSet($key, $value = false)
    {
        $data = $this->getCookie();

        if (!isset($data[$key])) {
            return false;
        }

        if (!$value) {
            return true;
        } else {
            return $data[$key] === $value;
        }
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function cookieGetValue($key)
    {
        $data = $this->getCookie();

        return $data[$key];
    }

    /**
     * @param string $key
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
        return json_decode(Mage::getSingleton('core/cookie')->get(self::COOKIE_NAME), true);
    }
}
