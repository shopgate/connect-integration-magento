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

namespace Shopgate\OAuth2\Storage;

use OAuth2\Storage\ClientCredentialsInterface;

interface FacebookCredentialsInterface extends ClientCredentialsInterface
{
    /**
     * @param string $username - username to get details for
     *
     * @return array|false     - the associated "user_id" and optional "scope" values
     *                           This function MUST return FALSE if the requested user does not exist or is
     *                           invalid. "scope" is a space-separated list of restricted scopes.
     * @code
     *     return array(
     *         "user_id"  => USER_ID,    // REQUIRED user_id to be stored with the authorization code or access token
     *         "scope"    => SCOPE       // OPTIONAL space-separated list of restricted scopes
     *     );
     */
    public function getUserDetails($username);
}
