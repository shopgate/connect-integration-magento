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

class Shopgate_Cloudapi_Model_Api2_Dispatcher extends Mage_Api2_Model_Dispatcher
{
    /**
     * Uses the "Header Version" as fallback.
     * If no Header Version is specified, uses the highest
     * version of the api2.xml for this resource.
     *
     * @inheritdoc
     */
    public function getVersion($resourceType, $requestedVersion)
    {
        return parent::getVersion($resourceType, $this->rewriteVersion($requestedVersion));
    }

    /**
     * Assuming path shopgate/V2/carts, pulls the version 2 and
     * uses that as the primary version
     *
     * @param string | bool $requestedVersion - fallback if unable to retrieve from URL
     *
     * @return string | bool
     */
    private function rewriteVersion($requestedVersion)
    {
        $path = Mage::getSingleton('api2/request')->getPathInfo();
        preg_match('/V(?P<ver>(?!1[0-5]\b)\d+)/', $path, $matches);

        return (isset($matches['ver']) && is_numeric($matches['ver'])) ? $matches['ver'] : $requestedVersion;
    }
}
