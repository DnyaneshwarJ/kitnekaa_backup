<?php

if (version_compare(Mage::getVersion(), '1.3.0', '>=')) {
    $eav = new Mage_Eav_Model_Entity_Setup('sales_setup');
    $eav->updateAttribute('catalog_product', 'udropship_vendor', 'used_in_product_listing', 1);
}