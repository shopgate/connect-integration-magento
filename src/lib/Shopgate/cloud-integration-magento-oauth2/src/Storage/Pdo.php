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

class Pdo extends \OAuth2\Storage\Pdo implements FacebookCredentialsInterface
{
    const AUTH_TOKEN_EXPIRE_SECONDS = 30;
    const AUTH_TYPE_CHECKOUT        = 'checkout';
    const AUTH_TOKEN_LENGTH         = 40;

    /**
     * Allows to customize which tables to install
     *
     * @param string $dbName
     *
     * @return string
     */
    public function getBuildSql($dbName = 'not used')
    {
        $sql = '';
        if (!empty($this->config['client_table'])) {
            $sql .= "
                CREATE TABLE IF NOT EXISTS {$this->config['client_table']} (
                  client_id             VARCHAR(80)   NOT NULL,
                  client_secret         VARCHAR(80),
                  redirect_uri          VARCHAR(2000),
                  grant_types           VARCHAR(80),
                  scope                 VARCHAR(4000),
                  user_id               VARCHAR(80),
                  PRIMARY KEY (client_id)
                );";
        }

        if (!empty($this->config['access_token_table'])) {
            $sql .= "
                CREATE TABLE IF NOT EXISTS {$this->config['access_token_table']} (
                  access_token         VARCHAR(40)    NOT NULL,
                  client_id            VARCHAR(80)    NOT NULL,
                  user_id              VARCHAR(80),
                  expires              TIMESTAMP      NOT NULL,
                  scope                VARCHAR(4000),
                  PRIMARY KEY (access_token)
                );";
        }

        if (!empty($this->config['code_table'])) {
            $sql .= "
                CREATE TABLE IF NOT EXISTS {$this->config['code_table']} (
                  authorization_code  VARCHAR(40)    NOT NULL,
                  client_id           VARCHAR(80)    NOT NULL,
                  user_id             VARCHAR(80),
                  redirect_uri        VARCHAR(2000),
                  expires             TIMESTAMP      NOT NULL,
                  scope               VARCHAR(4000),
                  id_token            VARCHAR(1000),
                  resource_id         VARCHAR(80),
                  resource_type       VARCHAR(255), 
                  PRIMARY KEY (authorization_code)
                );";
        }

        if (!empty($this->config['refresh_token_table'])) {
            $sql .= "
                CREATE TABLE IF NOT EXISTS {$this->config['refresh_token_table']} (
                  refresh_token       VARCHAR(40)    NOT NULL,
                  client_id           VARCHAR(80)    NOT NULL,
                  user_id             VARCHAR(80),
                  expires             TIMESTAMP      NOT NULL,
                  scope               VARCHAR(4000),
                  PRIMARY KEY (refresh_token)
                );";
        }

        if (!empty($this->config['user_table'])) {
            $sql .= "
                CREATE TABLE IF NOT EXISTS {$this->config['user_table']} (
                  username            VARCHAR(80),
                  password            VARCHAR(80),
                  first_name          VARCHAR(80),
                  last_name           VARCHAR(80),
                  email               VARCHAR(80),
                  email_verified      BOOLEAN,
                  scope               VARCHAR(4000)
                );";
        }

        if (!empty($this->config['scope_table'])) {
            $sql .= "
                CREATE TABLE IF NOT EXISTS {$this->config['scope_table']} (
                  scope               VARCHAR(80)  NOT NULL,
                  is_default          BOOLEAN,
                  PRIMARY KEY (scope)
                );";
        }

        if (!empty($this->config['jwt_table'])) {
            $sql .= "
                CREATE TABLE IF NOT EXISTS {$this->config['jwt_table']} (
                  client_id           VARCHAR(80)   NOT NULL,
                  subject             VARCHAR(80),
                  public_key          VARCHAR(2000) NOT NULL
                );";
        }

        if (!empty($this->config['jti_table'])) {
            $sql .= "
                CREATE TABLE IF NOT EXISTS {$this->config['jti_table']} (
                  issuer              VARCHAR(80)   NOT NULL,
                  subject             VARCHAR(80),
                  audience            VARCHAR(80),
                  expires             TIMESTAMP     NOT NULL,
                  jti                 VARCHAR(2000) NOT NULL
                );";
        }

        if (!empty($this->config['public_key_table'])) {
            $sql .= "
                CREATE TABLE IF NOT EXISTS {$this->config['public_key_table']} (
                  client_id            VARCHAR(80),
                  public_key           VARCHAR(2000),
                  private_key          VARCHAR(2000),
                  encryption_algorithm VARCHAR(100) DEFAULT 'RS256'
                );";
        }

        return $sql;
    }

    /**
     * @param string $authorizationCode
     * @param string $resourceType
     *
     * @return array | false
     */
    public function getAuthItemByTokenAndType($authorizationCode, $resourceType)
    {
        $stmt = $this->db->prepare(
            sprintf(
                'SELECT * from %s where authorization_code = :authorizationCode and resource_type = :resourceType',
                $this->config['code_table']
            )
        );
        $stmt->execute(compact('authorizationCode', 'resourceType'));

        $item = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($item) {
            $item['is_expired'] = strtotime($item['expires']) < time();
        }

        return $item;
    }

    /**
     * @param string     $resourceType - type of resource this is
     * @param int        $resourceId   - variable parameter, quoteId in case of checkout
     * @param string     $clientId     - CustomerNumber-ShopNumber
     * @param null | int $userId
     *
     * @return mixed
     */
    public function createAuthItemByType($resourceType, $resourceId, $clientId, $userId = null)
    {
        /** remove exist auth item */
        $this->unsetAuthItemByResourceIdAndType($resourceId, $resourceType);

        $token   = substr(hash('sha512', md5(microtime()) . $resourceType), 0, self::AUTH_TOKEN_LENGTH);
        $expires = date('Y-m-d H:i:s', time() + self::AUTH_TOKEN_EXPIRE_SECONDS);

        $stmt = $this->db->prepare(
            sprintf(
                'INSERT INTO %s (authorization_code, client_id, user_id, expires, resource_id, resource_type) VALUES (:token, :clientId, :userId, :expires, :resourceId, :resourceType)',
                $this->config['code_table']
            )
        );

        $stmt->execute(compact('token', 'clientId', 'userId', 'expires', 'resourceId', 'resourceType'));

        return $this->getAuthItemByTokenAndType($token, $resourceType);
    }

    /**
     * @param string $authorizationCode
     *
     * @return bool
     */
    public function unsetAuthItemByToken($authorizationCode)
    {
        $stmt = $this->db->prepare(
            sprintf('DELETE FROM %s WHERE authorization_code = :authorizationCode', $this->config['code_table'])
        );

        $stmt->execute(compact('authorizationCode'));

        return $stmt->rowCount() > 0;
    }

    /**
     * @param int    $resourceId
     * @param string $resourceType
     *
     * @return bool
     */
    public function unsetAuthItemByResourceIdAndType($resourceId, $resourceType)
    {
        $stmt = $this->db->prepare(
            sprintf(
                'DELETE FROM %s WHERE resource_id = :resourceId AND resource_type = :resourceType',
                $this->config['code_table']
            )
        );

        $stmt->execute(compact('resourceId', 'resourceType'));

        return $stmt->rowCount() > 0;
    }

    /**
     * Removes all expired resources from the 'code_table' table
     *
     * @return bool
     */
    public function removeExpiredResourceAuthCodes()
    {
        return $this->removeExpiredTokens($this->config['code_table']);
    }

    /**
     * Removes all expired resources from the 'access_token_table'
     *
     * @return bool
     */
    public function removeExpiredAccessTokens()
    {
        return $this->removeExpiredTokens($this->config['access_token_table']);
    }

    /**
     * Removes all expired resources from the 'refresh_token_table'
     *
     * @return bool
     */
    public function removeExpiredRefreshTokens()
    {
        return $this->removeExpiredTokens($this->config['refresh_token_table']);
    }

    /**
     * Helps remove data rows that are expired
     *
     * @param string $table
     *
     * @return bool - whether the action was successful
     */
    protected function removeExpiredTokens($table)
    {
        $now = date('Y-m-d H:i:s');

        return $this->db->exec(sprintf('DELETE FROM %s WHERE expires < "%s"', $table, $now)) > 0;
    }
}
