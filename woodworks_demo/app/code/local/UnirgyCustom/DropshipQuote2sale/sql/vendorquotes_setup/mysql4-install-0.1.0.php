<?php
$installer = $this;
$installer->startSetup();
$sql=<<<SQLTEXT
CREATE TABLE {$installer->getTable('dropshipquote2sale/vendorequotes')} (
	  `quote_request_id` int,
	  `vendor_id` varchar(255)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;

SQLTEXT;

$installer->run($sql);
$installer->endSetup();
	 