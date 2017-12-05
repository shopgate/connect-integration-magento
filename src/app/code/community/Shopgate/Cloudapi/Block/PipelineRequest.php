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

class Shopgate_Cloudapi_Block_PipelineRequest extends Mage_Adminhtml_Block_Template
{
    /**
     * Pipeline event checkout finished
     */
    const PIPELINE_REQUEST_CREATE_NEW_CART_FOR_CUSTOMER = 'createNewCartForCustomer';

    /**
     * Pipeline request serial
     */
    const PIPELINE_REQUEST_SERIAL = '4711';

    /**
     * @var array
     */
    protected $methods = array();

    /**
     * @return array
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * @return string
     */
    public function getJsonMethods()
    {
        return json_encode($this->getMethods());
    }

    /**
     * @param array $methods
     */
    public function setMethods($methods)
    {
        $this->methods = $methods;
    }

    /**
     * @param array $method
     */
    public function addMethod($method)
    {
        array_push($this->methods, $method);
    }

}
