<?php
$installer = $this;
$installer->startSetup();
$sql=<<<SQLTEXT
alter table kitnekaa_shopping_list_items add column delivery_location varchar(255);
SQLTEXT;

$installer->run($sql);
$installer->endSetup();
	 