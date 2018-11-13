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

class Shopgate_Cloudapi_Helper_Frontend_Request_Paypal
{
    const PAYPAL_CANCELLED = 'shopgate_connect_paypal_cancelled';

    /**
     * Sets a cookie to identify that we are coming from a cancelled PayPal page
     */
    public function setCancellation()
    {
        $this->getCookieFactory()->set(self::PAYPAL_CANCELLED, true);
    }

    /**
     * Checks if the PayPal cookie was set
     *
     * @return bool
     */
    public function isCancellation()
    {
        return $this->getCookieFactory()->get(self::PAYPAL_CANCELLED) === true;
    }

    /**
     * Delete the cookie
     */
    public function unsetCancellation()
    {
        $this->getCookieFactory()->set(self::PAYPAL_CANCELLED, false, 0);
    }

    /**
     * @return Mage_Core_Model_Cookie
     */
    private function getCookieFactory()
    {
        return Mage::getSingleton('core/cookie');
    }
}
