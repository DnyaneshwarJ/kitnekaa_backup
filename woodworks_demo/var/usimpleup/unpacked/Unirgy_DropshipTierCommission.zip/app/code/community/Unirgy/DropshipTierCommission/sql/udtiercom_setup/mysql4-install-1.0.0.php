<?php

/* @var $installer Mage_Sales_Model_Entity_Setup */
$installer = $this;
$conn = $this->_conn;
$installer->startSetup();

$conn->addColumn($this->getTable('udropship/vendor'), 'tiercom_rates', 'text');
$conn->addColumn($this->getTable('sales/shipment_item'), 'commission_percent', 'decimal(12,4)');

if (Mage::helper('udropship')->isModuleActive('udpo')) {
    $conn->addColumn($this->getTable('udpo/po_item'), 'commission_percent', 'decimal(12,4)');
}

$conn->dropKey($this->getTable('udropship/vendor_statement_row'), 'UNQ_PO_STATEMENT');
$conn->addColumn($this->getTable('udropship/vendor_statement_row'), 'po_item_id', 'int(10)');
$conn->addColumn($this->getTable('udropship/vendor_statement_row'), 'sku', 'varchar(128)');
$conn->addColumn($this->getTable('udropship/vendor_statement_row'), 'simple_sku', 'varchar(128)');
$conn->addColumn($this->getTable('udropship/vendor_statement_row'), 'vendor_sku', 'varchar(128)');
$conn->addColumn($this->getTable('udropship/vendor_statement_row'), 'vendor_simple_sku', 'varchar(128)');
$conn->addColumn($this->getTable('udropship/vendor_statement_row'), 'product', 'varchar(255)');
$conn->addKey($this->getTable('udropship/vendor_statement_row'), 'UNQ_POITEM_STATEMENT', array('po_id','po_type','statement_id','po_item_id'), 'unique');

if (Mage::helper('udropship')->isModuleActive('Unirgy_DropshipPayout')) {
    $conn->dropKey($this->getTable('udpayout/payout_row'), 'UNQ_PO_PAYOUT');
    $conn->addColumn($this->getTable('udpayout/payout_row'), 'po_item_id', 'int(10)');
    $conn->addColumn($this->getTable('udpayout/payout_row'), 'sku', 'varchar(128)');
    $conn->addColumn($this->getTable('udpayout/payout_row'), 'simple_sku', 'varchar(128)');
    $conn->addColumn($this->getTable('udpayout/payout_row'), 'vendor_sku', 'varchar(128)');
    $conn->addColumn($this->getTable('udpayout/payout_row'), 'vendor_simple_sku', 'varchar(128)');
    $conn->addColumn($this->getTable('udpayout/payout_row'), 'product', 'varchar(255)');
    $conn->addKey($this->getTable('udpayout/payout_row'), 'UNQ_POITEM_PAYOUT', array('po_id','po_type','payout_id','po_item_id'), 'unique');
}

$conn->addColumn($this->getTable('udropship/vendor'), 'tiercom_fixed_rule', 'varchar(128)');
$conn->addColumn($this->getTable('udropship/vendor'), 'tiercom_fixed_rates', 'text');
$conn->addColumn($this->getTable('sales/shipment_item'), 'transaction_fee', 'decimal(12,4)');

if (Mage::helper('udropship')->isModuleActive('udpo')) {
    $conn->addColumn($this->getTable('udpo/po_item'), 'transaction_fee', 'decimal(12,4)');
}

$installer->endSetup();
