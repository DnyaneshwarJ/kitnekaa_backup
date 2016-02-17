<?php
$installer = $this;
$installer->startSetup();
$sql=<<<SQLTEXT
alter table kitnekaa_shopping_list_items add column product_id int;
		
SQLTEXT;

$installer->run($sql);
$installer->endSetup();
	 