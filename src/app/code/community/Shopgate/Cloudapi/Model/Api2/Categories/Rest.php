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

class Shopgate_Cloudapi_Model_Api2_Categories_Rest extends Shopgate_Cloudapi_Model_Api2_Resource
{
    /** @noinspection PhpHierarchyChecksInspection */
    /**
     * An empty stub to be extendable by custom plugins until we implement the real function
     *
     * @throws Mage_Api2_Exception
     */
    protected function _retrieve()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        throw new Mage_Api2_Exception('The id API endpoint is not implemented', Mage_Api2_Model_Server::HTTP_NOT_FOUND);
    }

    /** @noinspection PhpHierarchyChecksInspection */
    /**
     * An empty stub to be extendable by custom plugins until we implement the real function
     *
     * @throws Mage_Api2_Exception
     */
    protected function _retrieveCollection()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        throw new Mage_Api2_Exception(
            'The collection API endpoint is not implemented',
            Mage_Api2_Model_Server::HTTP_NOT_FOUND
        );
    }
}
