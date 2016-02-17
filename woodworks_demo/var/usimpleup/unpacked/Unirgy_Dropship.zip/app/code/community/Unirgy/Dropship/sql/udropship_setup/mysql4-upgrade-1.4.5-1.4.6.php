<?php

$this->startSetup();

$qTable = $this->getTable('sales_flat_quote');
$qaTable = $this->getTable('sales_flat_quote_address');
$qiTable = $this->getTable('sales_flat_quote_item');
$qaiTable = $this->getTable('sales_flat_quote_address_item');

$conn = $this->_conn;
$eav = new Mage_Eav_Model_Entity_Setup('sales_setup');

if (!$conn->tableColumnExists($qaTable, 'udropship_shipping_details')) {
    $conn->addColumn($qaTable, 'udropship_shipping_details', 'text');
    $eav->addAttribute('quote_address', 'udropship_shipping_details', array('type'=>'static'));
}

if (!$conn->tableColumnExists($qaiTable, 'udropship_shipping_details')) {
    $conn->addColumn($qaiTable, 'udropship_vendor', 'int unsigned');
    $eav->addAttribute('quote_address_item', 'udropship_vendor', array('type'=>'static'));
}

if ($conn->tableColumnExists($qTable, 'udropship_shipping_details')) {
    $this->run("
    update $qaTable, $qTable
    set $qaTable.udropship_shipping_details=$qTable.udropship_shipping_details
    where $qaTable.quote_id=$qTable.entity_id and $qaTable.address_type='billing'
        and $qTable.udropship_shipping_details is not null and $qTable.udropship_shipping_details<>'';

    update $qTable set udropship_shipping_details='';

    update $qaiTable, $qiTable
    set $qaiTable.udropship_vendor=$qiTable.udropship_vendor
    where $qaiTable.quote_item_id=$qiTable.item_id
    ");
}

$this->endSetup();
