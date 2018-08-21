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
/** @noinspection PhpIncludeInspection */
require_once Mage::getModuleDir('controllers', 'Mage_Api') . DS . 'V2' . DS . 'SoapController.php';

class Shopgate_Cloudapi_V2Controller extends Mage_Api_V2_SoapController
{

    /**
     * Method that allows to define version in Header Version
     */
    public function indexAction()
    {
    }

    /**
     * Runs the customer login / token generation routine
     * Should be allowed to be run by the Customer or Admin role.
     */
    public function userAction()
    {
        $this->_getServer()->run();
    }

    /**
     * All calls related to cart action
     */
    public function cartsAction()
    {
        $this->_getServer()->run();
    }

    /**
     * All calls related to customers
     */
    public function customersAction()
    {
        $this->_getServer()->run();
    }

    /**
     * All calls related to wishlists
     */
    public function wishlistsAction()
    {
        $this->_getServer()->run();
    }

    /**
     * All calls related to product action
     */
    public function productsAction()
    {
        $this->_getServer()->run();
    }

    /**
     * All calls related to category information retrieval
     */
    public function categoriesAction()
    {
        $this->_getServer()->run();
    }

    /**
     * Forward token calls
     */
    public function authAction()
    {
        $this->_getServer()->run();
    }

    /**
     * Initializes the entry point and replaces the path to point to REST
     *
     * @throws Mage_Core_Model_Store_Exception
     * @throws Zend_Controller_Request_Exception
     */
    public function preDispatch()
    {
        // register psr-4 autoloader for cloud library
        Mage::getSingleton('shopgate_cloudapi/autoloader')->createAndRegister();

        if ($this->getCorsHelper()->isCorsCall()) {
            $this->getCorsHelper()->sendCorsHeaders();
            $this->setFlag('', Mage_Core_Controller_Front_Action::FLAG_NO_DISPATCH, true);

            return;
        }

        $request = Mage::getSingleton('api2/request');
        $request->setParam('store', Mage::app()->getStore()->getId());
        Mage::helper('shopgate_cloudapi/request')->setShopgateApi();

        parent::preDispatch();
        $this->replaceRestPath($request);
    }

    /**
     * Move api2 response into global response.
     * This is needed because the frontend controller
     * does not use the REST response Headers & other data.
     * When we return product data, the headers get sent
     * prematurely, so we avoid header manipulation then.
     *
     * @throws Zend_Controller_Response_Exception
     */
    public function postDispatch()
    {
        if ($this->getResponse()->canSendHeaders(false)) {
            $response    = $this->getCorsHelper()->getCorsResponse();
            $apiResponse = Mage::getSingleton('api2/response');
            foreach ($apiResponse->getHeaders() as $header) {
                $response->setHeader($header['name'], $header['value'], $header['replace']);
            }

            $response->setHttpResponseCode($apiResponse->getHttpResponseCode());
            Mage::app()->setResponse($response);
        }
        parent::postDispatch();
    }

    /**
     * Retrieve the server that handles authentication
     *
     * @return Shopgate_Cloudapi_Model_Server
     */
    protected function _getServer()
    {
        return Mage::getSingleton('shopgate_cloudapi/server');
    }

    /**
     * Replaces shopgate/ to forward the call to api/rest/.
     * This whole thing is needed because we would need to modify root
     * .htaccess to create a custom entry point instead of this controller.
     *
     * @param Mage_Api2_Model_Request $request
     */
    protected function replaceRestPath(Mage_Api2_Model_Request $request)
    {
        $path = $request->getPathInfo();
        $path = strstr($path, '/shopgate');
        $rest = 'api' . DS . Mage_Api2_Model_Server::API_TYPE_REST . DS;
        $path = str_replace('shopgate/', $rest, $path);
        $request->setPathInfo($path);
    }

    /**
     * This helper handles CORS logic (JS calls from browser)
     *
     * @return Shopgate_Cloudapi_Helper_Preflight
     */
    private function getCorsHelper()
    {
        return Mage::helper('shopgate_cloudapi/preflight');
    }
}
