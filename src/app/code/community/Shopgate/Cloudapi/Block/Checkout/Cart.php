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

class Shopgate_Cloudapi_Block_Checkout_Cart extends Mage_Core_Block_Template
{
    /**
     * Checks if we are coming from a PayPal cancellation controller
     * and delete the cookie that tracks it
     *
     * @return bool
     */
    public function isPaypalCancellation()
    {
        $isCancellation = $this->paypalHelper()->isCancellation();
        $this->paypalHelper()->unsetCancellation();

        return $isCancellation;
    }

    /**
     * @return Shopgate_Cloudapi_Helper_Frontend_Request_Paypal
     */
    private function paypalHelper()
    {
        return Mage::helper('shopgate_cloudapi/frontend_request_paypal');
    }
}
