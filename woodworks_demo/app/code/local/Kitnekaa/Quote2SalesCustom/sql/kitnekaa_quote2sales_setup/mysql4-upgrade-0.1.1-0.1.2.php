<?php
/* @var $installer Mage_Sales_Model_Entity_Setup */
$installer = new Mage_Sales_Model_Mysql4_Setup();
$installer->startSetup();
$installer->addAttribute("quote", "quote_request_by", array("type"=>"varchar"));
$installer->endSetup();