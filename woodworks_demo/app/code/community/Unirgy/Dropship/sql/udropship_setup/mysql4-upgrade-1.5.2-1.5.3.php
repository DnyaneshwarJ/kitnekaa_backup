<?php

$this->startSetup();

$this->_conn->addColumn($this->getTable('udropship_vendor_product'), 'vendor_sku', 'varchar(64)');
$this->_conn->addColumn($this->getTable('udropship_vendor_product'), 'vendor_cost', 'decimal(12,4)');
$this->_conn->addColumn($this->getTable('udropship_vendor_product'), 'stock_qty', 'decimal(12,4)');

$this->endSetup();