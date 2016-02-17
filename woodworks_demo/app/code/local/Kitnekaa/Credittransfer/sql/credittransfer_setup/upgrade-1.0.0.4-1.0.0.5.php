<?php
$installer = $this;
$installer->startSetup();
$installer->run("
ALTER TABLE `{$installer->getTable('credittransfer/docneeded')}` 
CHANGE `company_id` verifying_company_id INT NOT NULL;

");
$installer->endSetup();



