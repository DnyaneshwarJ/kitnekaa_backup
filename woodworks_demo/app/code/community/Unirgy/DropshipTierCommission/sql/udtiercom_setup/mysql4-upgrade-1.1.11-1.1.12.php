<?php

/* @var $installer Mage_Sales_Model_Entity_Setup */
$installer = $this;
$conn = $this->_conn;
$installer->startSetup();

$conn->addColumn($this->getTable('udropship/vendor_statement_row'), 'total_payment', 'decimal(12,4)');
$conn->addColumn($this->getTable('udropship/vendor_statement_row'), 'total_invoice', 'decimal(12,4)');

if (Mage::helper('udropship')->isModuleActive('Unirgy_DropshipPayout')) {
    $conn->addColumn($this->getTable('udpayout/payout_row'), 'total_payment', 'decimal(12,4)');
    $conn->addColumn($this->getTable('udpayout/payout_row'), 'total_invoice', 'decimal(12,4)');
}


$installer->endSetup();
