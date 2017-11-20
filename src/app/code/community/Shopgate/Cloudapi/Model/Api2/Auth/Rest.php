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

class Shopgate_Cloudapi_Model_Api2_Auth_Rest extends Shopgate_Cloudapi_Model_Api2_Resource
{
    /**
     * Picks up the attributes from the api2.xml config and compares
     * the incoming requested parameters against the 'attributes' node.
     * Need to figure out how to do "optional" parameters still.
     *
     * @todo-sg: redesign this part so that we can pass required parameters and optional parameters via api2.xml
     * @todo-sg: implement on resource class level
     * @see    Shopgate_Cloudapi_Model_Api2_Resource::dispatch()
     *
     * @param array $data - translated body parameters
     *
     * @throws Mage_Api2_Exception
     */
    protected function checkIncomingData(array $data)
    {
        $attributes = $this->getAvailableAttributes($this->getUserType(), $this->getOperation());
        if (count(array_diff_key($attributes, $data)) !== 0) {
            $this->_critical(self::RESOURCE_DATA_PRE_VALIDATION_ERROR);
        }
    }
}
