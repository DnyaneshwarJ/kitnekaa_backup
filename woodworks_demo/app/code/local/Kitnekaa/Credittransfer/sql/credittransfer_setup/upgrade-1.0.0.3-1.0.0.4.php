<?php
$installer = $this;
$installer->startSetup();
$installer->run("
ALTER TABLE `{$installer->getTable('credittransfer/docs')}` 
ADD `verifying_company_id` INT NOT NULL,
ADD `customer_id` INT NOT NULL
");
$installer->endSetup();



