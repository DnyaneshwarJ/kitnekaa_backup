<?php
$installer = $this;
$installer->startSetup();
$sql=<<<SQLTEXT
alter table {$installer->getTable('admin/user')} add vendor_id int null;
SQLTEXT;

$installer->run($sql);
$installer->endSetup();
	 