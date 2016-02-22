<?php
$installer = $this;
$installer->startSetup();
$sql=<<<SQLTEXT
create table docs(id int not null AUTO_INCREMENT,company_id int not null,doc_id int not null,doc_path varchar(255) not null,
	from_date varchar(255),
	to_date varchar(255),
	verified  INT(1) NOT NULL DEFAULT '0',
	actives  INT(1) NOT NULL DEFAULT '1', primary key(id));

SQLTEXT;

$installer->run($sql);
$installer->endSetup();
	 