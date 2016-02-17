<?php
$installer = $this;
$installer->startSetup();
$installer->run("
ALTER TABLE `{$installer->getTable('sales/quote_payment')}` 
ADD `cust_utr_number` VARCHAR( 255 ) NOT NULL,
ADD `cust_bank_name` VARCHAR( 255 ) NOT NULL;
  
ALTER TABLE `{$installer->getTable('sales/order_payment')}` 
ADD `cust_utr_number` VARCHAR( 255 ) NOT NULL,
ADD `cust_bank_name` VARCHAR( 255 ) NOT NULL;
");
$installer->endSetup();