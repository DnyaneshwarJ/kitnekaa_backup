<?php
/* @var $installer Mage_Sales_Model_Entity_Setup */
$installer = new Mage_Sales_Model_Mysql4_Setup();
$installer->startSetup();
$installer->addAttribute("order", "vendor_id", array("type"=>"int"));
$installer->endSetup();