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

abstract class Shopgate_Cloudapi_Model_Api2_Carts_Items_Rest extends Shopgate_Cloudapi_Model_Api2_Carts_Utility
{
    const COUPON_ID      = 'COUPON_';
    const FAULT_MODULE   = 'Mage_Checkout';
    const FAULT_RESOURCE = 'cart_product';

    /** @noinspection PhpHierarchyChecksInspection */
    /**
     * Adds items(s) to the customer's quote
     *
     * @param array $filteredData - item data after filtration was applied
     *
     * @throws Mage_Api2_Exception
     * @throws Exception
     */
    public function _multiCreate(array $filteredData)
    {
        if ($this->getRequest()->getParam('itemId')) {
            $this->_critical('Invalid request body', Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        }

        $this->handleRequest($filteredData);
        $this->_successMessage('Successfully updated the cart', Mage_Api2_Model_Server::HTTP_OK);
    }

    /** @noinspection PhpHierarchyChecksInspection */
    /**
     * Updates items in cart
     *
     * @inheritdoc
     * @return stdClass
     *
     * @throws Exception
     * @throws Mage_Api2_Exception
     */
    public function _create(array $filteredData)
    {
        $cartItem = $this->getRequest()->getParam('itemId');
        $quote    = $this->loadUserQuote();
        $item     = $quote->getItemById($cartItem);
        if (null === $item) {
            $this->_critical('Cart item does not exist', Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        }

        if (isset($filteredData['cartItemId'])) {
            $filteredData['cartItemId'] = $cartItem;
        } else {
            $filteredData = array_merge(array('cartItemId' => $cartItem), $filteredData);
        }

        $this->handleRequest(array($filteredData));

        return new stdClass();
    }

    /** @noinspection PhpHierarchyChecksInspection */
    /**
     * Delete multiple items from the cart
     *
     * @param array $requestData
     *
     * @throws Mage_Api2_Exception
     * @throws Exception
     */
    public function _multiDelete(array $requestData)
    {
        $cartItem = $this->getRequest()->getParam('itemId');
        if (!empty($cartItem)) {
            $requestData['cartItemIds'] = $cartItem;
        }

        $cartItemIds = array();
        if (isset($requestData['cartItemIds'])) {
            $trim        = trim($requestData['cartItemIds'], ',');
            $cartItemIds = array_map('trim', explode(',', $trim));
        }

        $quote = $this->loadUserQuote();
        $this->removeItemsFromQuote($quote, $cartItemIds);
        $quote->collectTotals()->save();
    }

    /**
     * Helper function that removes items from quote and
     * checks that the specified items were removed.
     * Removes all items if cartIds are not specified.
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param array                  $cartIds - cart items to remove, if empty - removes all items
     *
     * @return int - returns the new item count inside quote
     * @throws Mage_Api2_Exception
     */
    protected function removeItemsFromQuote(Mage_Sales_Model_Quote $quote, array $cartIds)
    {
        $removeCount = count(array_filter($cartIds, 'is_numeric')); //skip counting coupons
        $oldCount    = count($quote->getAllItems());

        foreach ($cartIds as $cartItemId) {
            if (strpos($cartItemId, self::COUPON_ID) === 0) {
                $quote->setCouponCode('');
            } else {
                $quote->removeItem($cartItemId);
            }
        }

        if (empty($cartIds)) {
            //no need to clear coupons, collector -> no items -> remove coupons
            $quote->removeAllItems();
        }

        $newCount = count($quote->getAllItems());
        if (($oldCount - $removeCount) < $newCount) {
            $this->_critical(
                'Could not remove all cart items: ' . implode(',', $cartIds),
                Mage_Api2_Model_Server::HTTP_BAD_REQUEST
            );
        }

        return $newCount;
    }

    /**
     * Handles the incoming cart item request.
     * There is a transaction reverted in case
     * there is a fatal error with one of the
     * products during the process. We want none
     * of the items added in that case.
     *
     * @param array $filteredData
     *
     * @throws Mage_Api2_Exception
     */
    protected function handleRequest(array $filteredData)
    {
        $quote   = $this->loadUserQuote();
        $db      = Mage::getSingleton('core/resource')->getConnection('core_write');
        $factory = Mage::getModel('shopgate_cloudapi/api2_carts_factory');
        $list    = $factory->translateCartItems($filteredData, $this->_getStore());
        $db->beginTransaction();

        try {
            $list->apply($quote->getId());
            $db->commit();
        } catch (Mage_Api_Exception $e) {
            $error = $this->getFault(
                $e->getMessage(),
                'Fault: ' . $e->getMessage() . ' ' . $e->getCustomMessage(),
                $this->getResourceFromMessage($e->getMessage())
            );
            $db->rollBack();
            $this->_critical($error, Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        } catch (Mage_Api2_Exception $e) {
            $db->rollBack();
            $this->_critical($e->getMessage(), $e->getCode());
        } catch (Exception $e) {
            $db->rollBack();
            $this->_critical($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * Just checks if the error has any mentioning of coupons
     * and returns the appropriate resource to translate error
     *
     * @param string $error
     *
     * @return string
     */
    private function getResourceFromMessage($error)
    {
        return strpos($error, 'coupon') !== false ? 'cart_coupon' : self::FAULT_RESOURCE;
    }

    /**
     * Loads the Customer/Guest quote model
     * for manipulation
     *
     * @return Mage_Sales_Model_Quote
     */
    abstract protected function loadUserQuote();
}
