<?php
$installer = $this;
$installer->startSetup();
$sql=<<<SQLTEXT
create table verifying_company(verifying_company_id int not null AUTO_INCREMENT PRIMARY KEY,verifying_company_name varchar(255) not null
	 );
SQLTEXT;

$installer->run($sql);
$installer->endSetup();
	 