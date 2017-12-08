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

class Shopgate_Cloudapi_Helper_Frontend_Template extends Mage_Core_Helper_Abstract
{
    /**
     * Session variable indicating that this purchase belongs to Shopgate
     */
    const SESSION_IS_SHOPGATE_REQUEST = 'is_shopgate_checkout';

    /**
     * Non-framed responsive template we can use
     * in the app
     */
    const PAGE_TEMPLATE_EMPTY = 'page/empty.phtml';

    /**
     * Returns the checkout page template based on
     * shopgate session flag set in the observer
     *
     * @return string
     */
    public function getShopgatePageTemplate()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->isShopgateRequest()
            ? self::PAGE_TEMPLATE_EMPTY
            : Mage::app()->getLayout()->getBlock('root')->getTemplate();
    }

    /**
     * @return bool
     */
    public function isShopgateRequest()
    {
        return Mage::getSingleton('checkout/session')->getData(self::SESSION_IS_SHOPGATE_REQUEST);
    }
}
