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
     * Name and value of cookie created for sg cloud requests
     */
    const COOKIE_NAME = 'shopgate';
    const COOKIE_VALUE = '1';

    /**
     * @return bool
     */
    public function isShopgateRequest()
    {
        if ($this->parameterDetected()) {
            $this->setCookie();

            return true;
        }

        return $this->cookieIsSet();
    }

    /**
     * @return bool
     */
    protected function parameterDetected()
    {
        return Mage::app()->getRequest()->getParam(self::KEY_SGCLOUD_INAPP) === self::COOKIE_VALUE;
    }

    /**
     * Will set the shopgate cookie
     */
    protected function setCookie()
    {
        Mage::getSingleton('core/cookie')->set(self::COOKIE_NAME, self::COOKIE_VALUE, 0);
    }

    /**
     * @return bool
     */
    protected function cookieIsSet()
    {
        return Mage::getSingleton('core/cookie')->get(self::COOKIE_NAME) === self::COOKIE_VALUE;
    }
}
