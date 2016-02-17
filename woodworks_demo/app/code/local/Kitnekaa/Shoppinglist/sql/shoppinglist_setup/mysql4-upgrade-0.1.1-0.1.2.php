<?php
$installer = $this;
$installer->startSetup();
$sql=<<<SQLTEXT
alter table kitnekaa_shopping_list_items add column price float,add column attributes text;
SQLTEXT;

$installer->run($sql);
$installer->endSetup();
	 