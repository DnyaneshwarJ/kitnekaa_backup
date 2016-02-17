<?php

$cEav = new Mage_Catalog_Model_Resource_Eav_Mysql4_Setup('catalog_setup');

if (version_compare(Mage::getVersion(), '1.4.0', '>=')) {
    $cEav->updateAttribute('catalog_product', 'udropship_vendor', 'is_used_for_price_rules', 1);
}