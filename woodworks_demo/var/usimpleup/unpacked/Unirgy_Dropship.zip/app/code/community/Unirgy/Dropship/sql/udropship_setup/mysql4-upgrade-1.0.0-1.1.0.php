<?php

$eav = new Mage_Eav_Model_Entity_Setup('sales_setup');
$eav->updateAttribute('catalog_product', 'udropship_vendor', 'is_required', 1);