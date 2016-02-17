<?php
$installer = $this;
$installer->startSetup();
$installer->run("
ALTER TABLE `quote2sales_requests` ADD COLUMN company_id int;
ALTER TABLE `quote2sales_requests` ADD COLUMN billing_address_id int DEFAULT null;
");
$installer->endSetup();
