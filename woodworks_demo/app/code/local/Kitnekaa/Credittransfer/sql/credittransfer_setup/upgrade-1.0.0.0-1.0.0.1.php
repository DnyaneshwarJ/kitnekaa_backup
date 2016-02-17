<?php
$installer = $this;
$installer->startSetup();
$sql=<<<SQLTEXT
create table doc_name(doc_id int not null AUTO_INCREMENT,doc_name varchar(255) not null,
	 has_time_period INT(1) NOT NULL DEFAULT '0',primary key(doc_id));
SQLTEXT;

$installer->run($sql);
$installer->endSetup();
	 