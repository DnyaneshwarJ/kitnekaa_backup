<?php
/* @var $installer Mage_Sales_Model_Entity_Setup */
$installer = new Mage_Sales_Model_Mysql4_Setup();
$installer->startSetup();
$installer->addAttribute("order", "company_id", array("type"=>"int"));
$installer->addAttribute("order", "company_name", array("type"=>"varchar"));
$installer->endSetup();