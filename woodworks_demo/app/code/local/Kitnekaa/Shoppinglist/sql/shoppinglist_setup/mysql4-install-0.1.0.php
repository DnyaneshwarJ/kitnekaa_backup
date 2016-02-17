<?php
$installer = $this;
$installer->startSetup();
$sql=<<<SQLTEXT
CREATE TABLE kitnekaa_shopping_list (
	  `list_id` int unsigned NOT NULL auto_increment,
	  `list_name` varchar(255) NOT NULL default '',
	  `company_id` text NOT NULL default '',
	  `status` tinyint(2) NOT NULL default '0',
	  `created_time` datetime NULL,
	  `update_time` datetime NULL,
	  `created_by` int,
	  `updated_by` int,
	  PRIMARY KEY (`list_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;

	create table kitnekaa_shopping_list_items(
    id int unsigned NOT NULL auto_increment,
    list_id int  ,
    sku varchar(255),
    item_name VARCHAR(255),
    description VARCHAR (255),
    qty FLOAT ,
    need_date datetime,
    frequency VARCHAR(255),
    target_price DOUBLE,
    comment longtext,
    attachment VARCHAR (255),
    added_by int,
    PRIMARY KEY (`id`)
	)ENGINE=InnoDB DEFAULT CHARSET=utf8;
		
SQLTEXT;

$installer->run($sql);
//demo 
//Mage::getModel('core/url_rewrite')->setId(null);
//demo 
$installer->endSetup();
	 