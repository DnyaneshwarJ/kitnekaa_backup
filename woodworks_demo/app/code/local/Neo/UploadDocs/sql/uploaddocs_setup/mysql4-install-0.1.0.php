<?php
$installer = $this;
$installer->startSetup();
$sql=<<<SQLTEXT
create table company_documents(id int not null auto_increment, file_name varchar(255) not null,type varchar(255) not null, company_id int not null,customer_id int not null, primary key(id));
SQLTEXT;

$installer->run($sql);
//demo 
//Mage::getModel('core/url_rewrite')->setId(null);
//demo 
$installer->endSetup();
	 