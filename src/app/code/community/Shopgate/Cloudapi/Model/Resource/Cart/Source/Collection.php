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

class Shopgate_Cloudapi_Model_Resource_Cart_Source_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init('shopgate_cloudapi/cart_source');
    }

    /**
     * @param int|string $quoteId
     * @param string     $source
     *
     * @return Shopgate_Cloudapi_Model_Resource_Cart_Source_Collection
     */
    public function setQuoteFilter($quoteId, $source = Shopgate_Cloudapi_Model_Cart_Source::SOURCE_SHOPGATE_APP)
    {
        $this->addFieldToFilter('quote_id', $quoteId)
             ->addFieldToFilter('source', $source);

        return $this;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return $this->getSize() === 0;
    }
}
