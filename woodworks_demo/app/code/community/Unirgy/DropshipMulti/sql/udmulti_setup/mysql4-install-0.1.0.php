<?php

$this->startSetup();

$qiTable = $this->getTable('sales_flat_quote_item');
$qaiTable = $this->getTable('sales_flat_quote_address_item');

$conn = $this->_conn;
$sEav = new Mage_Sales_Model_Mysql4_Setup('sales_setup');

if (!$conn->tableColumnExists($qiTable, 'cost')) {
    $conn->addColumn($qiTable, 'cost', 'decimal(12,4)');
    if (!Mage::helper('udropship')->isSalesFlat()) {
        $sEav->addAttribute('quote_item', 'cost', array('type'=>'static'));
    }
}

if (!$conn->tableColumnExists($qaiTable, 'cost')) {
    $conn->addColumn($qaiTable, 'cost', 'decimal(12,4)');
    if (!Mage::helper('udropship')->isSalesFlat()) {
        $sEav->addAttribute('quote_address_item', 'cost', array('type'=>'static'));
    }
}

$this->endSetup();