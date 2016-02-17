<?php

$this->startSetup();

$conn = $this->_conn;

$t = $this->getTable('udropship_vendor_shipping');
$dups = $conn->fetchAll("SELECT vendor_id, shipping_id, count(*) cnt, group_concat(vendor_shipping_id SEPARATOR ',') ids FROM {$t} GROUP BY vendor_id, shipping_id HAVING cnt>1");
if ($dups) {
    $dupIds = array();
    foreach ($dups as $r) {
        $ids = explode(',', $r['ids']);
        array_shift($ids);
        $dupIds = array_merge($dupIds, $ids);
    }
    if ($dupIds) {
        $conn->delete($t, $conn->quoteInto('vendor_shipping_id in (?)', $dupIds));
    }
}

$conn->addKey($t, 'IDX_VENDOR_SHIPPING', array('vendor_id', 'shipping_id'), 'unique');

$eav = new Mage_Eav_Model_Entity_Setup('sales_setup');
$eav->addAttribute('shipment_track', 'udropship_status', array('type' => 'varchar'));

$this->endSetup();