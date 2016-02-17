<?php
/**
 *
 * @category   Taxcation
 * @package    Nest_Taxcation
 * @author     Vatsal
 */

$installer = $this;

$installer->startSetup();

$installer->run("
    ALTER TABLE  `".$this->getTable('sales/quote_address')."` ADD  `nest_tax_name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'Nest Tax Name';
    ALTER TABLE  `".$this->getTable('sales/quote_address')."` ADD  `nest_tax_total_amount` DECIMAL( 10, 4 ) NULL DEFAULT '0.0000' COMMENT 'Nest Tax Total Amount';

	ALTER TABLE  `".$this->getTable('sales/quote_item')."` ADD  `nest_tax_percent` DECIMAL( 10, 4 ) NULL DEFAULT '0.0000' COMMENT 'Base Tax Percent';
	ALTER TABLE  `".$this->getTable('sales/quote_item')."` ADD  `nest_tax_amount` DECIMAL( 10, 4 ) NULL DEFAULT '0.0000' COMMENT 'Base Tax Amount';    

    ALTER TABLE  `".$this->getTable('sales/order')."` ADD  `nest_tax_name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'Nest Tax Name';
    ALTER TABLE  `".$this->getTable('sales/order')."` ADD  `nest_tax_total_amount` DECIMAL( 10, 4 ) NULL DEFAULT '0.0000' COMMENT 'Nest Tax Total Amount';

    ALTER TABLE  `".$this->getTable('sales/order_item')."` ADD  `nest_tax_percent` DECIMAL( 10, 4 ) NULL DEFAULT '0.0000' COMMENT 'Base Tax Percent';
    ALTER TABLE  `".$this->getTable('sales/order_item')."` ADD  `nest_tax_amount` DECIMAL( 10, 4 ) NULL DEFAULT '0.0000' COMMENT 'Base Tax Amount';

    ALTER TABLE  `".$this->getTable('sales/invoice')."` ADD  `nest_tax_name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'Nest Tax Name';
    ALTER TABLE  `".$this->getTable('sales/invoice')."` ADD  `nest_tax_total_amount` DECIMAL( 10, 4 ) NULL DEFAULT '0.0000' COMMENT 'Nest Tax Total Amount';

    ALTER TABLE  `".$this->getTable('sales/invoice_item')."` ADD  `nest_tax_percent` DECIMAL( 10, 4 ) NULL DEFAULT '0.0000' COMMENT 'Base Tax Percent';
    ALTER TABLE  `".$this->getTable('sales/invoice_item')."` ADD  `nest_tax_amount` DECIMAL( 10, 4 ) NULL DEFAULT '0.0000' COMMENT 'Base Tax Amount';

    ALTER TABLE  `".$this->getTable('sales/creditmemo')."` ADD  `nest_tax_name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'Nest Tax Name';
    ALTER TABLE  `".$this->getTable('sales/creditmemo')."` ADD  `nest_tax_total_amount` DECIMAL( 10, 4 ) NULL DEFAULT '0.0000' COMMENT 'Nest Tax Total Amount';

    ALTER TABLE  `".$this->getTable('sales/creditmemo_item')."` ADD  `nest_tax_percent` DECIMAL( 10, 4 ) NULL DEFAULT '0.0000' COMMENT 'Base Tax Percent';
    ALTER TABLE  `".$this->getTable('sales/creditmemo_item')."` ADD  `nest_tax_amount` DECIMAL( 10, 4 ) NULL DEFAULT '0.0000' COMMENT 'Base Tax Amount';
    
");

$installer->endSetup(); 