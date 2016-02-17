<?php
$installer = $this;
$installer->startSetup();
$sql=<<<SQLTEXT
CREATE TABLE kitnekaa_shopping_list_files (
	  `file_id` int unsigned NOT NULL auto_increment,
	  `file_name` varchar(255) NOT NULL default '',
	  `list_item_id` int,
	  PRIMARY KEY (`file_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SQLTEXT;

$installer->run($sql);
$installer->endSetup();
