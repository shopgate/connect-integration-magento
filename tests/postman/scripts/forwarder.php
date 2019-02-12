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
 * The sole purpose of this script is to pass down all
 * newman (browser) calls to the shell scripts.
 */
$main = validate();
$path = __DIR__ . "/shell/shopgate_cloudapi_{$main}.php";
mimicShell($path);

try {
    /** @noinspection PhpIncludeInspection */
    include_once $path;
} catch (Exception $e) {
    echo $e->getMessage();
}

/**
 * Checking integrity
 *
 * @return string - returns the main path name
 */
function validate()
{
    $whitelist = array('quote', 'db', 'config');
    $keys      = array_keys($_GET);
    $route     = array_shift($keys);
    if (!in_array($route, $whitelist, true)) {
        throw new RuntimeException('Incorrect command passed. Cannot forward.');
    }

    return $route;
}

/**
 * Pretend like we are calling the
 * shell not from the browser
 *
 * @param string $path - the main shell script (can be any string to be honest)
 */
function mimicShell($path)
{
    $_SERVER['argv'][] = $path;
    unset($_SERVER['REQUEST_METHOD']);
    foreach ($_GET as $key => $value) {
        $_SERVER['argv'][] = $key;
        $_SERVER['argv'][] = $value;
    }
}
