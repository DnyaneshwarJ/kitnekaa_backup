<?php

/* @var $installer Mage_Sales_Model_Entity_Setup */
$installer = $this;
$conn = $this->_conn;
$installer->startSetup();

$conn->dropKey($this->getTable('udropship/vendor_statement_refund_row'), 'UNQ_PO_STATEMENT_RR');
$conn->addColumn($this->getTable('udropship/vendor_statement_refund_row'), 'po_item_id', 'int(10)');
$conn->addColumn($this->getTable('udropship/vendor_statement_refund_row'), 'refund_item_id', 'int(10)');
$conn->addColumn($this->getTable('udropship/vendor_statement_refund_row'), 'sku', 'varchar(128)');
$conn->addColumn($this->getTable('udropship/vendor_statement_refund_row'), 'simple_sku', 'varchar(128)');
$conn->addColumn($this->getTable('udropship/vendor_statement_refund_row'), 'vendor_sku', 'varchar(128)');
$conn->addColumn($this->getTable('udropship/vendor_statement_refund_row'), 'vendor_simple_sku', 'varchar(128)');
$conn->addColumn($this->getTable('udropship/vendor_statement_refund_row'), 'product', 'varchar(255)');
$conn->addKey($this->getTable('udropship/vendor_statement_refund_row'), 'UNQ_POITEM_STATEMENT_RR', array('refund_id', 'po_id','po_type','statement_id','po_item_id'), 'unique');

$installer->endSetup();
