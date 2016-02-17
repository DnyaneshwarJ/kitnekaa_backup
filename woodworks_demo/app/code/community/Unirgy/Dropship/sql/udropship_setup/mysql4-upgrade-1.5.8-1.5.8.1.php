<?php

$this->startSetup();

$eav = new Mage_Eav_Model_Entity_Setup('sales_setup');

$eav->addAttribute('shipment', 'udropship_vendor_order_id', array('type' => 'varchar'));

$this->endSetup();