<?php
$installer = $this;
$installer->startSetup();
$sql=<<<SQLTEXT
alter table kitnekaa_shopping_list_items add column billing_address_id int;
SQLTEXT;

$installer->run($sql);
$installer->endSetup();
	 