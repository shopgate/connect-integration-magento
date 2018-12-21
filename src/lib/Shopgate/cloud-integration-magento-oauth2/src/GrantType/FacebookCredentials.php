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

namespace Shopgate\OAuth2\GrantType;

use OAuth2\ClientAssertionType\HttpBasic;
use OAuth2\GrantType\GrantTypeInterface;
use OAuth2\RequestInterface;
use OAuth2\ResponseInterface;
use OAuth2\ResponseType\AccessTokenInterface;
use Shopgate\OAuth2\Storage\FacebookCredentialsInterface;

/**
 * @see HttpBasic
 */
class FacebookCredentials extends HttpBasic implements GrantTypeInterface
{
    /** @var array */
    private $userInfo;
    /** @var FacebookCredentialsInterface */
    protected $storage;

    /**
     * @param FacebookCredentialsInterface $storage
     * @param array                        $config
     */
    public function __construct(FacebookCredentialsInterface $storage, array $config = array())
    {
        /**
         * The client credentials grant type MUST only be used by confidential clients
         *
         * @see http://tools.ietf.org/html/rfc6749#section-4.4
         */
        $config['allow_public_clients'] = false;

        parent::__construct($storage, $config);
    }

    /**
     * Get query string identifier
     *
     * @return string
     */
    public function getQueryStringIdentifier()
    {
        return 'facebook';
    }

    /**
     * Get user id
     *
     * @return mixed
     */
    public function getUserId()
    {
        return $this->userInfo['user_id'];
    }

    /**
     * Get scope
     *
     * @return null
     */
    public function getScope()
    {
        return null;
    }

    /**
     * Create access token
     *
     * @param AccessTokenInterface $accessToken
     * @param mixed                $client_id - client identifier related to the access token.
     * @param mixed                $user_id   - user id associated with the access token
     * @param string               $scope     - scopes to be stored in space-separated string.
     *
     * @return array
     */
    public function createAccessToken(AccessTokenInterface $accessToken, $client_id, $user_id, $scope)
    {
        /**
         * Client Credentials Grant does NOT include a refresh token
         *
         * @see http://tools.ietf.org/html/rfc6749#section-4.4.3
         */
        $includeRefreshToken = false;

        return $accessToken->createAccessToken($client_id, $user_id, $scope, $includeRefreshToken);
    }

    /**
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     *
     * @return bool|mixed|null
     *
     * @throws \LogicException
     */
    public function validateRequest(RequestInterface $request, ResponseInterface $response)
    {
        if (!$request->request('user_id')) {
            $response->setError(400, 'invalid_request', 'Required parameter "user_id" is missing');

            return null;
        }

        if (!filter_var($request->request('user_id'), FILTER_VALIDATE_EMAIL)) {
            $response->setError(400, 'invalid_request', 'Parameter "user_id" is not a valid email');

            return null;
        }

        $userInfo = $this->storage->getUserDetails($request->request('user_id'));
        if (empty($userInfo)) {
            $response->setError(401, 'invalid_grant', 'Unable to retrieve user information');

            return null;
        }

        if (!isset($userInfo['user_id'])) {
            throw new \LogicException('You must set the "user_id" on the array returned by getUserDetails');
        }

        $this->userInfo = $userInfo;

        return parent::validateRequest($request, $response);
    }
}
