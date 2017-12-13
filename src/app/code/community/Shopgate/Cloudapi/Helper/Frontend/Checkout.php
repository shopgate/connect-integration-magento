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

class Shopgate_Cloudapi_Helper_Frontend_Checkout extends Mage_Core_Helper_Abstract
{
    /**
     * Non-framed responsive template we can use
     * in the app
     */
    const PAGE_TEMPLATE_EMPTY = 'page/empty.phtml';

    /**
     * Controller and action path for redirect
     */
    const AUTH_CHECKOUT_URL = 'shopgate-checkout/quote/auth/';

    /**
     * Session variable indicating that this purchase belongs to Shopgate
     */
    const SESSION_IS_SHOPGATE_CHECKOUT = 'is_shopgate_checkout';

    /**
     * Returns the checkout page template based on
     * shopgate session flag set in the observer
     *
     * @return string
     */
    public function getCheckoutPageTemplate()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->isShopgateCheckout()
            ? self::PAGE_TEMPLATE_EMPTY
            : Mage::app()->getLayout()->getBlock('root')->getTemplate();
    }

    /**
     * Login customer using the email provided
     *
     * @param string                $email
     * @param Mage_Core_Model_Store $store
     */
    public function loginByEmail($email, $store)
    {
        $customer = Mage::getModel('customer/customer')->setStore($store)->loadByEmail($email);

        if ($customer->getId()) {
            Mage::getSingleton('customer/session')->loginById($customer->getId());
        }
    }

    /**
     * Logs out current customer
     */
    public function logoutCustomer()
    {
        Mage::getSingleton('customer/session')->logout();
    }

    /**
     * @param int          $resourceId
     * @param int | string $storeId
     * @param int | null   $customerId
     *
     * @return Shopgate_Cloudapi_Model_Auth_Code
     * @throws Mage_Core_Exception
     * @throws Mage_Core_Model_Store_Exception
     */
    public function generateAuthToken($resourceId, $storeId, $customerId)
    {
        /** @var Magento_Db_Adapter_Pdo_Mysql $writeConnection */
        $writeConnection = Mage::getSingleton('core/resource')->getConnection('core_write');
        /** @var Shopgate_Cloudapi_Model_OAuth2_Db_Pdo $storage */
        $storage = Mage::getModel('shopgate_cloudapi/oAuth2_db_pdo', array($writeConnection->getConnection()));
        $storage->setStore(Mage::app()->getStore($storeId));

        $token = $storage->createAuthItemByType(
            \Shopgate\OAuth2\Storage\Pdo::AUTH_TYPE_CHECKOUT,
            $resourceId,
            $storage->getClientId(),
            $customerId
        );

        return $token;
    }

    /** @noinspection PhpDocMissingThrowsInspection */
    /**
     * Throws a checkout exception
     *
     * @param string $message
     * @param int    $code
     *
     * @throws Shopgate_Cloudapi_Model_Frontend_Checkout_Exception
     */
    public function throwException($message, $code = 0)
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        throw Mage::exception('Shopgate_Cloudapi_Model_Frontend_Checkout', $this->__($message), $code);
    }

    /**
     * @return bool
     */
    public function isShopgateCheckout()
    {
        $session = Mage::getSingleton('checkout/session');

        return $session->getData(self::SESSION_IS_SHOPGATE_CHECKOUT);
    }
}
