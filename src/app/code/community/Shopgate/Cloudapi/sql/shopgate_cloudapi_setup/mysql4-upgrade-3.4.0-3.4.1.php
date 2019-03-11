<?php

/** @var Shopgate_Cloudapi_Model_Resource_Setup $installer */
$installer = $this;
$installer->startSetup();
$connection = $installer->getConnection();
$orderTable = $installer->getTable('shopgate_order_sources');

if ($connection->tableColumnExists(
        $orderTable,
        Shopgate_Cloudapi_Model_Resource_Order_Source::COLUMN_AGENT
    ) === false
) {
    $connection->addColumn(
        $orderTable,
        Shopgate_Cloudapi_Model_Resource_Order_Source::COLUMN_AGENT,
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
            'nullable' => true,
            'comment'  => 'User Agent',
        )
    );
}

$indexes = $connection->getIndexList($orderTable);

if (array_key_exists('FK_SHOPGATE_ORDER_SOURCES_ORDER_ID_SALES_FLAT_ORDER_ENTITY_ID', $indexes)) {
    $index = $indexes['FK_SHOPGATE_ORDER_SOURCES_ORDER_ID_SALES_FLAT_ORDER_ENTITY_ID'];
    if (is_array($index['fields']) && is_array(
            $index['fields'][0] != Shopgate_Cloudapi_Model_Resource_Order_Source::COLUMN_ORDER_ID
        )
    ) {
        $connection->dropIndex(
            $orderTable,
            'FK_SHOPGATE_ORDER_SOURCES_ORDER_ID_SALES_FLAT_ORDER_ENTITY_ID'
        );
        $connection->addIndex(
            $orderTable,
            'FK_SHOPGATE_ORDER_SOURCES_ORDER_ID_SALES_FLAT_ORDER_ENTITY_ID',
            array(Shopgate_Cloudapi_Model_Resource_Order_Source::COLUMN_ORDER_ID),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        );
    }
}

try {
    /**
     * Add REST ACL attributes
     */
    $installer->getAclAttributeHelper()->addAclAttributes(Mage_Api2_Model_Auth_User_Customer::USER_TYPE);
    $installer->getAclAttributeHelper()->addAclAttributes(Mage_Api2_Model_Auth_User_Admin::USER_TYPE);

    /**
     * Add REST admin role rules & customer
     */
    $role = $installer->getAclRoleHelper()->getAdminRole();
    $installer->getAclRuleHelper()->addAclRules($role->getId());
    $installer->getAclRuleHelper()->addAclRules();
} catch (Exception $e) {
    Mage::logException($e);
}

$installer->endSetup();

