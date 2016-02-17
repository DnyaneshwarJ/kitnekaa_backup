<?php
$installer = $this;
$installer->startSetup();
$sql=<<<SQLTEXT
CREATE TABLE kitnekaa_company (
	  `company_id` int unsigned NOT NULL auto_increment,
	  `company_name` varchar(255),
	  PRIMARY KEY (`company_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;

SQLTEXT;

$installer->run($sql);
$installer->endSetup();
	 