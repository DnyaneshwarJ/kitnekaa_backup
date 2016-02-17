<?php

/**
 * Created on 24th May 2015
 *
 * @author Bobcares
 * @desc Creating necessary tables for quote2sales module and adding
 * the custom attribute to the product table.
 */
echo 'Running Bobcares Quote2Sales Install 0.8.5: ' . get_class($this) . "\n <br /> \n";
$installer = $this;
$installer->startSetup();
$installer->run("
DROP TABLE IF EXISTS `{$this->getTable('quote2sales_requests')}`;
DROP TABLE IF EXISTS `{$this->getTable('quote2sales_requests_status')}`;

CREATE TABLE IF NOT EXISTS `{$this->getTable('quote2sales_requests')}` (
  `request_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `name` varchar(250) DEFAULT NULL,
  `email` varchar(250) DEFAULT NULL,
  `phone` varchar(100) DEFAULT NULL,
  `comment` text,
  `status` varchar(50) DEFAULT NULL,  
  `product_id` int(11) unsigned DEFAULT NULL,
  `seller_comment` text,
  PRIMARY KEY (`request_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=15 ;

CREATE TABLE IF NOT EXISTS `{$this->getTable('quote2sales_requests_status')}` (
    `status_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `request_id` int(11) unsigned DEFAULT NULL,
    `quote_id` int(11) unsigned DEFAULT NULL,
    `order_id` int(11) unsigned DEFAULT NULL,
    `status` varchar(50) DEFAULT NULL,  
     PRIMARY KEY (`status_id`)
    );
");

/* Creating the custom attribute to enable and disable reuest for quote option in the 
 * product 
 */
$customProductAttributeSetup = Mage::getResourceModel('catalog/setup', 'catalog_setup');
$customProductAttributeSetup->startSetup();
$customProductAttributeSetup->addAttribute('catalog_product', 'is_display_request_for_quote', array(
    'group' => 'General',
    'type' => 'int',
    'backend' => '',
    'frontend' => '',
    'label' => 'Display request for quote',
    'input' => 'boolean',
    'class' => '',
    'source' => '',
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible' => true,
    'required' => false,
    'user_defined' => true,
    'used_in_product_listing' => true,
    'default' => '',
    'searchable' => true,
    'filterable' => true,
    'comparable' => true,
    'visible_on_front' => true,
    'unique' => false,
    'apply_to' => 'simple,configurable,bundle,grouped,virtual,downloadable',
    'is_configurable' => true
));
$customProductAttributeSetup->endSetup();


echo "Done Running setup \n";
$installer->endSetup();
