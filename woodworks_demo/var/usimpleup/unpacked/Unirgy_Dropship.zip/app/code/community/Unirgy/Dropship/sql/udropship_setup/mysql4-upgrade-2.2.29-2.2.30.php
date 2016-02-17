<?php

/* @var $installer Mage_Sales_Model_Entity_Setup */
$installer = $this;
$conn = $this->_conn;
$installer->startSetup();

$conn->addColumn($this->getTable('udropship/vendor_statement'), 'total_payment', 'decimal(12,4)');
$conn->addColumn($this->getTable('udropship/vendor_statement'), 'total_invoice', 'decimal(12,4)');
$conn->addColumn($this->getTable('udropship/vendor_statement'), 'payment_due', 'decimal(12,4)');
$conn->addColumn($this->getTable('udropship/vendor_statement'), 'invoice_due', 'decimal(12,4)');
$conn->addColumn($this->getTable('udropship/vendor_statement'), 'payment_paid', 'decimal(12,4)');
$conn->addColumn($this->getTable('udropship/vendor_statement'), 'invoice_paid', 'decimal(12,4)');

$installer->endSetup();
