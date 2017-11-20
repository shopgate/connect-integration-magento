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

class Shopgate_Cloudapi_Model_Api2_Carts_Url_Rest_Admin_V2 extends Shopgate_Cloudapi_Model_Api2_Carts_Url_Rest
{
    /**
     * Loads provided quote as long as it does
     * not belong to a customer
     *
     * @return Mage_Sales_Model_Quote
     * @throws Exception
     * @throws Mage_Api2_Exception
     */
    protected function loadUserQuote()
    {
        $this->validateGuestCartId();
        $quoteId = $this->getRequest()->getParam('cartId');
        $quote   = $this->loadQuoteById($quoteId);
        $this->validateGuestQuote($quote);

        return $quote;
    }
}
