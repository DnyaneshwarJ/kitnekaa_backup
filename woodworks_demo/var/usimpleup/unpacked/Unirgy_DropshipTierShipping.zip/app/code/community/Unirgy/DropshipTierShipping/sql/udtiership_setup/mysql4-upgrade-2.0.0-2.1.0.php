<?php

/* @var $installer Mage_Sales_Model_Entity_Setup */
$installer = $this;
$conn = $this->_conn;
$installer->startSetup();

$keyList = $conn->getIndexList($this->getTable('udtiership_simple_cond_rates'));
if (!isset($keyList['UNQ_TS_SIMPLE_COND_DEL_CUS'])) {
    $this->run("
ALTER IGNORE TABLE `{$this->getTable('udtiership_simple_cond_rates')}`
ADD UNIQUE KEY `UNQ_TS_SIMPLE_COND_DEL_CUS` (delivery_type_id,customer_shipclass_id(255));
");
}

$keyList = $conn->getIndexList($this->getTable('udtiership_vendor_simple_cond_rates'));
if (!isset($keyList['UNQ_TS_SIMPLE_COND_VEN_DEL_CUS'])) {
    $this->run("
ALTER IGNORE TABLE `{$this->getTable('udtiership_vendor_simple_cond_rates')}`
ADD UNIQUE KEY `UNQ_TS_SIMPLE_COND_VEN_DEL_CUS` (vendor_id,delivery_type_id,customer_shipclass_id(255));
");
}

$keyList = $conn->getIndexList($this->getTable('udtiership_simple_rates'));
if (!isset($keyList['UNQ_TS_SIMPLE_DEL_CUS'])) {
    $this->run("
ALTER IGNORE TABLE `{$this->getTable('udtiership_simple_rates')}`
ADD UNIQUE KEY `UNQ_TS_SIMPLE_DEL_CUS` (delivery_type_id,customer_shipclass_id(255));
");
}

$keyList = $conn->getIndexList($this->getTable('udtiership_vendor_simple_rates'));
if (!isset($keyList['UNQ_TS_SIMPLE_VEN_DEL_CUS'])) {
    $this->run("
ALTER IGNORE TABLE `{$this->getTable('udtiership_vendor_simple_rates')}`
ADD UNIQUE KEY `UNQ_TS_SIMPLE_VEN_DEL_CUS` (vendor_id,delivery_type_id,customer_shipclass_id(255));
");
}

$keyList = $conn->getIndexList($this->getTable('udtiership_rates'));
if (!isset($keyList['UNQ_TS_RATES_DEL_CUS_CAT'])) {
    $this->run("
ALTER IGNORE TABLE `{$this->getTable('udtiership_rates')}`
ADD UNIQUE KEY `UNQ_TS_RATES_DEL_CUS_CAT` (delivery_type_id,vendor_shipclass_id(255),customer_shipclass_id(255),category_ids(255));
");
}

$keyList = $conn->getIndexList($this->getTable('udtiership_vendor_rates'));
if (!isset($keyList['UNQ_TS_RATES_VEN_DEL_CUS_CAT'])) {
    $this->run("
ALTER IGNORE TABLE `{$this->getTable('udtiership_vendor_rates')}`
ADD UNIQUE KEY `UNQ_TS_RATES_VEN_DEL_CUS_CAT` (vendor_id,delivery_type_id,customer_shipclass_id(255),category_ids(255));
");
}


$this->run("

CREATE TABLE IF NOT EXISTS `{$this->getTable('udtiership_product_rates')}` (
  `rate_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(10) unsigned NOT NULL,
  `delivery_type_id` int(10) unsigned NOT NULL,
  `customer_shipclass_id` text,
  `cost` decimal(12,4) NOT NULL DEFAULT '0.0000',
  `additional` decimal(12,4) NOT NULL DEFAULT '0.0000',
  `handling` decimal(12,4) NOT NULL DEFAULT '0.0000',
  `sort_order` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`rate_id`),
  UNIQUE KEY `UNQ_TS_PROD_RATES_PROD_DEL_CUS` (product_id,delivery_type_id,customer_shipclass_id(255)),
  KEY `FK_TS_PROD_RATE_DELIVERY_TYPE_ID` (`delivery_type_id`),
  KEY `FK_TS_PROD_RATE_PROD_ID` (`product_id`),
  CONSTRAINT `FK_TS_PROD_RATE_DELIVERY_TYPE_ID` FOREIGN KEY (`delivery_type_id`) REFERENCES `{$this->getTable('udtiership_delivery_type')}` (`delivery_type_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_TS_PROD_RATE_PROD_ID` FOREIGN KEY (`product_id`) REFERENCES `{$this->getTable('catalog/product')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

");

$eav = new Mage_Catalog_Model_Resource_Eav_Mysql4_Setup('catalog_setup');
$rAttr  = $eav->getAttribute('catalog_product', 'udtiership_rates');
$pET = $eav->getEntityType('catalog_product');

$prodRatesSelect = $conn->select()
    ->from(array('prod_rates' => $this->getTable($pET['entity_table']).'_'.$rAttr['backend_type']), array('entity_id', 'value'))
    ->where('prod_rates.attribute_id=?', $rAttr['attribute_id']);

$prodRates = $conn->fetchAll($prodRatesSelect);

if (is_array($prodRates)) {
    foreach ($prodRates as $prodRate) {
        $decoded = $prodRate['value'];
        if (!is_array($decoded)) {
            $decoded = Mage::helper('udropship')->unserialize($decoded);
        }
        if (!is_array($decoded)) {
            $decoded = array();
        }
        if (!empty($decoded)) {
            foreach ($decoded as &$__d) {
                $__d['delivery_type_id'] = $__d['delivery_type'];
            }
            unset($__d);
            Mage::helper('udtiership')->saveProductV2Rates($prodRate['entity_id'], $decoded);
        }
    }
}

$conn->delete($this->getTable($pET['entity_table']).'_'.$rAttr['backend_type'], array('attribute_id=?'=>$rAttr['attribute_id']));

$installer->endSetup();
