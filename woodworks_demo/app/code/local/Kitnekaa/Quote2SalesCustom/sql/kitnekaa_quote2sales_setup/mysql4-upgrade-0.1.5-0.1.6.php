<?php
$installer = $this;
$installer->startSetup();
$installer->run("
ALTER TABLE `quote2sales_requests` ADD COLUMN request_type VARCHAR(50) DEFAULT 'Product';
");
$installer->endSetup();