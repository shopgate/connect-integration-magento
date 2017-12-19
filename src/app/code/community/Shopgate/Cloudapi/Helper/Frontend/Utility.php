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

class Shopgate_Cloudapi_Helper_Frontend_Utility extends Mage_Core_Helper_Abstract
{
    /**
     * Config path for styles update
     */
    const CONFIG_PATH_CSS_PATTERN = 'shopgate_cloudapi/layout/styles';

    /**
     * @return false|string
     */
    public function getStyles()
    {
        if (Mage::helper('shopgate_cloudapi/request')->isShopgateRequest()) {
            $styleContent = trim(Mage::getStoreConfig(self::CONFIG_PATH_CSS_PATTERN));

            return $styleContent !== '' ? $styleContent : false;
        }

        return false;
    }
}
