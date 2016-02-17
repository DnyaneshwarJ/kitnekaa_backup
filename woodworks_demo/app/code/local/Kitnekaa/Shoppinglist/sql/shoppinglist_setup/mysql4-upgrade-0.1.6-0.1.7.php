<?php
$installer = $this;
$installer->startSetup();
$sql=<<<SQLTEXT
alter table kitnekaa_shopping_list modify created_by varchar(255);
alter table kitnekaa_shopping_list modify updated_by varchar(255);
alter table kitnekaa_shopping_list_items modify added_by varchar(255);
SQLTEXT;

$installer->run($sql);
$installer->endSetup();
	 