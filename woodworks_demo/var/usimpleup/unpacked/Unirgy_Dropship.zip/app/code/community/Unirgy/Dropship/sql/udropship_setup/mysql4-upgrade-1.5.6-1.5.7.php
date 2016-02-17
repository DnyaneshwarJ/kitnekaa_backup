<?php

$this->startSetup();

$eav = new Mage_Eav_Model_Entity_Setup('sales_setup');

$eav->addAttribute('shipment', 'base_tax_amount', array('type' => 'decimal'));
$eav->addAttribute('shipment', 'total_cost', array('type' => 'decimal'));
$eav->addAttribute('shipment', 'transaction_fee', array('type' => 'decimal'));
$eav->addAttribute('shipment', 'commission_percent', array('type' => 'decimal'));
$eav->addAttribute('shipment', 'handling_fee', array('type' => 'decimal'));

$this->endSetup();