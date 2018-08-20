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

/**
 * @method Shopgate_Cloudapi_Model_Cart_Source setQuoteId(int $quoteId)
 * @method int getQuoteId()
 * @method Shopgate_Cloudapi_Model_Cart_Source setSource(string $source)
 * @method string getSource()
 * @method _init($string)
 */
class Shopgate_Cloudapi_Model_Cart_Source extends Mage_Core_Model_Abstract
{
    const SOURCE_SHOPGATE_APP = 'shopgate_app';

    /**
     * Init model
     */
    protected function _construct()
    {
        $this->_init('shopgate_cloudapi/cart_source');
    }

    /**
     * Creates an entry for the shopgate app
     *
     * @param string $quoteId - cart entity id
     * @param string $source  - the source this save belongs to
     */
    public function saveQuote($quoteId, $source = self::SOURCE_SHOPGATE_APP)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $this->setSource($source)
             ->setQuoteId($quoteId)
             ->save();
    }
}
