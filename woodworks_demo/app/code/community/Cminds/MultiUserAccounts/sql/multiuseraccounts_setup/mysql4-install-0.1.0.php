<?php
/**
 * @author CreativeMindsSolutions
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->run("
        DROP TABLE IF EXISTS {$this->getTable('cminds_multiuseraccounts/subAccount')};
        CREATE TABLE IF NOT EXISTS `{$this->getTable('cminds_multiuseraccounts/subAccount')}` (
            `entity_id` int(11) NOT NULL auto_increment,
            `parent_customer_id` INT(10) UNSIGNED NOT NULL,
            `firstname` varchar(64) default NULL,
            `lastname` varchar(64) default NULL,
            `password_hash` varchar(256) default NULL,
            `email` varchar(128) default NULL,
            `permission` INT(1),
            `confirmation` varchar(256),
            `store_id` SMALLINT(5) default NULL,
            `website_id` SMALLINT(5) default NULL,
            `rp_token` VARCHAR(256) default NULL,
            `rp_token_created_at` DATETIME NOT NULL default '0000-00-00 00:00:00',
            PRIMARY KEY (`entity_id`),
            FOREIGN KEY (`parent_customer_id`) REFERENCES {$this->getTable('customer/entity')}(`entity_id`)
            ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");

$installer->endSetup();