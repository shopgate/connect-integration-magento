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

require_once __DIR__ . '/abstract.php';

/** @noinspection AutoloadingIssuesInspection */

class Shopgate_Cloudapi_Quote_Shell extends Mage_Shell_Abstract
{
    /** @var int | string */
    private $store;

    /**
     * @inheritdoc
     */
    public function __construct()
    {
        parent::__construct();
        $this->store = $this->getArg('store') ? : 1;
    }

    /**
     * Run SG script
     *
     * @throws Exception
     */
    public function run()
    {
        if ($this->getArg('quote')) {
            $quoteId = $this->getArg('quote');

            $quote = is_numeric($quoteId)
                ? $this->loadQuoteById($quoteId)
                : Mage::getModel('sales/quote')->setStoreId($this->store);

            if ($this->getArg('customer')) {
                /** @var Mage_Customer_Model_Customer $customer */
                $customer = Mage::getModel('customer/customer')->load($this->getArg('customer'));
                if (!$customer->getId()) {
                    throw new RuntimeException('Customer could not be loaded by the provided ID');
                }
                $quote->assignCustomer($customer);
                $quote->save();
            }

            if ($this->getArg('product')) {
                $productId = $this->getArg('product');
                $qty       = $this->getArg('qty') ? : 1;
                $quoteId   = $quote->getId() ? : $quote->save()->getId();
                Mage::getModel('checkout/cart_product_api')->add(
                    $quoteId,
                    array(array('product_id' => $productId, 'qty' => $qty))
                );
            }
            echo $quote->getId();

            return;
        }

        $this->usageHelp();
    }

    /**
     * Loads quote by provided ID
     *
     * @param string $quoteId - numeric quote ID
     *
     * @return Mage_Sales_Model_Quote
     * @throws Mage_Core_Model_Store_Exception
     */
    protected function loadQuoteById($quoteId)
    {
        return Mage::getModel('sales/quote')
                   ->setStore(Mage::app()->getStore($this->store))
                   ->load($quoteId);
    }

    /**
     * Parse input arguments
     *
     * @return Mage_Shell_Abstract
     */
    protected function _parseArgs()
    {
        if ($_SERVER['argv'][1] === 'help') {
            $this->_args['help'] = true;

            return $this;
        }
        if (count($_SERVER['argv']) % 2 === 0) {
            throw new RuntimeException('Need to have an even amount of parameters passed to this shell');
        }

        $size = count($_SERVER['argv']) - 1;
        for ($i = 1; $i <= $size; $i += 2) {
            $value1               = $_SERVER['argv'][$i];
            $value2               = $_SERVER['argv'][$i + 1];
            $this->_args[$value1] = $value2;
        }

        return $this;
    }

    /**
     * Retrieve argument value by name or false
     *
     * @param string $name the argument name
     *
     * @return mixed
     */
    public function getArg($name)
    {
        return !empty($this->_args[$name]) ? $this->_args[$name] : false;
    }

    /**
     * @return string
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f shopgate_cloudapi.php -- [options]
  quote [ID] store [ID]             Non-numeric quote ID will create a new quote instead. Store can be provided with all calls.
  quote [ID] customer [ID]          Adds a customer to quote using their ID. 
  quote [ID] product [ID]           Adds a product to quote using the products's ID
  quote [ID] product [ID] qty [#]   Can specify quantity of product
  help                              This help
USAGE;
    }
}

try {
    $shell = new Shopgate_Cloudapi_Quote_Shell();
    $shell->run();
} catch (Exception $e) {
    echo $e->getMessage();
}

