<?php
$installer = $this;
$installer->startSetup();
$installer->run("
ALTER TABLE `{$this->getTable('quote2sales_requests_products')}` ADD COLUMN item_options longtext;
");
$installer->endSetup();