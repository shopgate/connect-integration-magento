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

class Shopgate_Cloudapi_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Core_config_data paths
     */
    const PATH_AUTH_CUSTOMER_NUMBER = 'shopgate_cloudapi/authentication/customer_number';
    const PATH_AUTH_SHOP_NUMBER     = 'shopgate_cloudapi/authentication/shop_number';
    const PATH_AUTH_API_KEY         = 'shopgate_cloudapi/authentication/api_key';
    const PATH_LAYOUT_STYLES        = 'shopgate_cloudapi/layout/styles';

    /**
     * Observer disabling
     */
    const PATH_OBSERVERS_WISHLISTS_RETRIEVE       = 'shopgate_cloudapi/observers/wishlists_retrieve';
    const PATH_OBSERVERS_WISHLISTS_CREATE         = 'shopgate_cloudapi/observers/wishlists_create';
    const PATH_OBSERVERS_WISHLISTS_ADD_ITEM       = 'shopgate_cloudapi/observers/wishlists_add_item';
    const PATH_OBSERVERS_WISHLISTS_RETRIEVE_ITEMS = 'shopgate_cloudapi/observers/wishlists_retrieve_items';
}
