<?php
/* @var $installer Mage_Sales_Model_Entity_Setup */
$installer = new Mage_Sales_Model_Mysql4_Setup();
$installer->startSetup();
$installer->addAttribute("quote", "company_id", array("type"=>"int"));
$installer->addAttribute("quote", "company_name", array("type"=>"varchar"));
$installer->addAttribute("quote", "quote_by", array("type"=>"varchar"));
$installer->endSetup();