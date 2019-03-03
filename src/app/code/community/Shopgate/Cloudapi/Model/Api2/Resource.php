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
 * This class is a utility class for Resource specific rewrites
 * @method void _multiDelete(array $requestData) - deletion of a collection
 * @method array | void _multiCreate(array $filteredData)
 */
class Shopgate_Cloudapi_Model_Api2_Resource extends Mage_Api2_Model_Resource
{
    /**
     * This is the module name from where we can pull the Fault data
     *
     * @see Shopgate_Cloudapi_Model_Api2_Resource::getFault()
     */
    const FAULT_MODULE = '';

    /**
     * Defines the resource for the faults, e.g cart, cart_product, cart_customer, etc.
     *
     * @see Shopgate_Cloudapi_Model_Api2_Resource::getFault()
     */
    const FAULT_RESOURCE = '';

    /** @var Shopgate_Cloudapi_Model_Acl_Filter */
    protected $filter;

    /**
     * Hack rewrite of the main dispatch so that we
     * can pass empty POST requests
     *
     * @inheritdoc
     * @throws Exception
     * @throws Mage_Api2_Exception
     */
    public function dispatch()
    {
        switch ($this->getActionType() . $this->getOperation()) {
            /* Create */
            case self::ACTION_TYPE_ENTITY . self::OPERATION_CREATE:
                /**
                 * Passthrough to update a single entity as PUT does
                 * not load body content in PHP5.6<
                 */
                $this->setOperation(self::OPERATION_UPDATE);
                $this->dispatch();
                break;
            case self::ACTION_TYPE_COLLECTION . self::OPERATION_CREATE:
                // If no of the methods(multi or single) is implemented, request body is not checked
                if (!$this->_checkMethodExist('_create') && !$this->_checkMethodExist('_multiCreate')) {
                    $this->_critical(self::RESOURCE_METHOD_NOT_IMPLEMENTED);
                }
                // If one of the methods(multi or single) is implemented, request body must not be empty
                $requestData = $this->getRequest()->getBodyParams();
                // The create action has the dynamic type which depends on data in the request body
                if (empty($requestData) || $this->getRequest()->isAssocArrayInRequestBody()) {
                    $this->_errorIfMethodNotExist('_create');
                    $filteredData = $this->getFilter()->in($requestData);
                    //If all incoming data is filtered, request is not good. However, the request can be empty.
                    if (empty($filteredData) && !empty($requestData)) {
                        $this->_critical(self::RESOURCE_REQUEST_DATA_INVALID);
                    }
                    $return = $this->_create($filteredData);
                    $this->_render($return ? : $this->getMessages());
                } else {
                    $this->_errorIfMethodNotExist('_multiCreate');
                    $filteredData = $this->getFilter()->collectionIn($requestData);
                    $return       = $this->_multiCreate($filteredData);
                    $this->_render($return ? : $this->getMessages());
                }
                break;
            /* Retrieve */
            case self::ACTION_TYPE_ENTITY . self::OPERATION_RETRIEVE:
                $this->_errorIfMethodNotExist('_retrieve');
                $return = $this->_retrieve();
                //todo-sg: TD, bring back filtration, tests needed for all retrieving
                //$filteredData = $this->getFilter()->out($return);
                $this->_render($return);
                break;
            case self::ACTION_TYPE_COLLECTION . self::OPERATION_RETRIEVE:
                $this->_errorIfMethodNotExist('_retrieveCollection');
                $return = $this->_retrieveCollection();
                //$filteredData  = $this->getFilter()->collectionOut($return);
                $this->_render($return);
                break;
            /* Update */
            case self::ACTION_TYPE_ENTITY . self::OPERATION_UPDATE:
                $this->_errorIfMethodNotExist('_update');
                $requestData = $this->getRequest()->getBodyParams();
                if (empty($requestData)) {
                    $this->_critical(self::RESOURCE_REQUEST_DATA_INVALID);
                }
                $filteredData = $this->getFilter()->in($requestData);
                if (empty($filteredData)) {
                    $this->_critical(self::RESOURCE_REQUEST_DATA_INVALID);
                }
                $this->_update($filteredData);
                break;
            case self::ACTION_TYPE_COLLECTION . self::OPERATION_UPDATE:
                $this->_errorIfMethodNotExist('_multiUpdate');
                $requestData = $this->getRequest()->getBodyParams();
                if (empty($requestData)) {
                    $this->_critical(self::RESOURCE_REQUEST_DATA_INVALID);
                }
                $filteredData = $this->getFilter()->collectionIn($requestData);
                $this->_multiUpdate($filteredData);
                $this->_render($this->getMessages());
                break;
            /* Delete */
            case self::ACTION_TYPE_ENTITY . self::OPERATION_DELETE:
                $this->_errorIfMethodNotExist('_delete');
                $this->_delete();
                $this->_render($this->getMessages());
                break;
            case self::ACTION_TYPE_COLLECTION . self::OPERATION_DELETE:
                $this->_errorIfMethodNotExist('_multiDelete');
                $requestData  = $this->getRequest()->getParams();
                $filteredData = $this->getFilter()->in($requestData);
                $this->_multiDelete($filteredData);
                $this->_render($this->getMessages());
                break;
            default:
                $this->_critical(self::RESOURCE_METHOD_NOT_IMPLEMENTED);
                break;
        }
    }

    /**
     * Accesses the SOAP API fault messages as we are utilizing some of the SOAP logic
     *
     * @param string      $code     - fault message
     * @param string|null $fallback - fallback error to return if the fault does not match
     * @param string|null $resource - rewrite of resource in special cases
     *
     * @return string - returns the error which matches the fault or returns fallback variable
     */
    public function getFault($code, $fallback = null, $resource = null)
    {
        /** @var array $faults */
        $resource = null !== $resource ? $resource : $this::FAULT_RESOURCE;
        $path     = Mage::getModuleDir('etc', $this::FAULT_MODULE) . DS . 'api.xml';
        $faults   = Mage::getModel('api/config', $path)->getFaults($resource);

        return (string) isset($faults[$code]) ? $faults[$code]['message'] : $fallback;
    }

    /**
     * @inheritdoc
     * @return Shopgate_Cloudapi_Model_Acl_Filter
     */
    public function getFilter()
    {
        if (!$this->filter) {
            $this->filter = Mage::getModel('shopgate_cloudapi/acl_filter', $this);
        }

        return $this->filter;
    }

    /**
     * Helps printing out error messages
     *
     * @return array|stdClass|
     * @throws Exception
     */
    private function getMessages()
    {
        return $this->getResponse()->hasMessages()
            ? array('messages' => $this->getResponse()->getMessages())
            : new stdClass();
    }
}
