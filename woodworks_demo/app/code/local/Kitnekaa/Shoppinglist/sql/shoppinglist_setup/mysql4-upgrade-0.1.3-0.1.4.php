<?php
$installer = $this;
$installer->startSetup();
$sql=<<<SQLTEXT
alter table kitnekaa_shopping_list_items modify need_date varchar(255);
SQLTEXT;

$installer->run($sql);
$installer->endSetup();
	 