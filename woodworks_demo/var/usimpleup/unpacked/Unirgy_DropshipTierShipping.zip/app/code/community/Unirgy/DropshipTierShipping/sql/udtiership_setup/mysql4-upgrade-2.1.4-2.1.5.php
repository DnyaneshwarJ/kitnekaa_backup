<?php

/* @var $installer Mage_Sales_Model_Entity_Setup */
$installer = $this;
$conn = $this->_conn;
$installer->startSetup();

$tsTables = array('udtiership_simple_rates', 'udtiership_vendor_simple_rates', 'udtiership_simple_cond_rates', 'udtiership_vendor_simple_cond_rates', 'udtiership_rates', 'udtiership_vendor_rates','udtiership_product_rates');

foreach ($tsTables as $tsTable) {
    $conn->addColumn($this->getTable($tsTable), 'customer_group_id', 'varchar(255) default "*"');
}

$keyList = $conn->getIndexList($this->getTable('udtiership_simple_cond_rates'));
if (isset($keyList['UNQ_TS_SIMPLE_COND_DEL_CUS'])) {
    $this->run("
ALTER IGNORE TABLE `{$this->getTable('udtiership_simple_cond_rates')}`
DROP KEY `UNQ_TS_SIMPLE_COND_DEL_CUS`;
");
}
    $this->run("
ALTER IGNORE TABLE `{$this->getTable('udtiership_simple_cond_rates')}`
ADD UNIQUE KEY `UNQ_TS_SIMPLE_COND_DEL_CUS` (delivery_type_id,customer_shipclass_id(255),customer_group_id);
");

$keyList = $conn->getIndexList($this->getTable('udtiership_vendor_simple_cond_rates'));
if (isset($keyList['UNQ_TS_SIMPLE_COND_VEN_DEL_CUS'])) {
    $this->run("
ALTER IGNORE TABLE `{$this->getTable('udtiership_vendor_simple_cond_rates')}`
DROP KEY `UNQ_TS_SIMPLE_COND_VEN_DEL_CUS`;
");
}
    $this->run("
ALTER IGNORE TABLE `{$this->getTable('udtiership_vendor_simple_cond_rates')}`
ADD UNIQUE KEY `UNQ_TS_SIMPLE_COND_VEN_DEL_CUS` (vendor_id,delivery_type_id,customer_shipclass_id(255),customer_group_id);
");

$keyList = $conn->getIndexList($this->getTable('udtiership_simple_rates'));
if (isset($keyList['UNQ_TS_SIMPLE_DEL_CUS'])) {
    $this->run("
ALTER IGNORE TABLE `{$this->getTable('udtiership_simple_rates')}`
DROP KEY `UNQ_TS_SIMPLE_DEL_CUS`;
");
}
    $this->run("
ALTER IGNORE TABLE `{$this->getTable('udtiership_simple_rates')}`
ADD UNIQUE KEY `UNQ_TS_SIMPLE_DEL_CUS` (delivery_type_id,customer_shipclass_id(255),customer_group_id);
");

$keyList = $conn->getIndexList($this->getTable('udtiership_vendor_simple_rates'));
if (isset($keyList['UNQ_TS_SIMPLE_VEN_DEL_CUS'])) {
    $this->run("
ALTER IGNORE TABLE `{$this->getTable('udtiership_vendor_simple_rates')}`
DROP KEY `UNQ_TS_SIMPLE_VEN_DEL_CUS`;
");
}
    $this->run("
ALTER IGNORE TABLE `{$this->getTable('udtiership_vendor_simple_rates')}`
ADD UNIQUE KEY `UNQ_TS_SIMPLE_VEN_DEL_CUS` (vendor_id,delivery_type_id,customer_shipclass_id(255),customer_group_id);
");

$keyList = $conn->getIndexList($this->getTable('udtiership_rates'));
if (isset($keyList['UNQ_TS_RATES_DEL_CUS_CAT'])) {
    $this->run("
ALTER IGNORE TABLE `{$this->getTable('udtiership_rates')}`
DROP KEY `UNQ_TS_RATES_DEL_CUS_CAT`;
");
}
    $this->run("
ALTER IGNORE TABLE `{$this->getTable('udtiership_rates')}`
ADD UNIQUE KEY `UNQ_TS_RATES_DEL_CUS_CAT` (delivery_type_id,vendor_shipclass_id(255),customer_shipclass_id(255),category_ids(255),customer_group_id);
");

$keyList = $conn->getIndexList($this->getTable('udtiership_vendor_rates'));
if (isset($keyList['UNQ_TS_RATES_VEN_DEL_CUS_CAT'])) {
    $this->run("
ALTER IGNORE TABLE `{$this->getTable('udtiership_vendor_rates')}`
DROP KEY `UNQ_TS_RATES_VEN_DEL_CUS_CAT`;
");
}

    $this->run("
ALTER IGNORE TABLE `{$this->getTable('udtiership_vendor_rates')}`
ADD UNIQUE KEY `UNQ_TS_RATES_VEN_DEL_CUS_CAT` (vendor_id,delivery_type_id,customer_shipclass_id(255),category_ids(255),customer_group_id);
");

$keyList = $conn->getIndexList($this->getTable('udtiership_product_rates'));
if (isset($keyList['UNQ_TS_PROD_RATES_PROD_DEL_CUS'])) {
    $this->run("
ALTER IGNORE TABLE `{$this->getTable('udtiership_product_rates')}`
DROP KEY `UNQ_TS_PROD_RATES_PROD_DEL_CUS`;
");
}
$this->run("
ALTER IGNORE TABLE `{$this->getTable('udtiership_product_rates')}`
ADD UNIQUE KEY `UNQ_TS_PROD_RATES_PROD_DEL_CUS` (product_id,delivery_type_id,customer_shipclass_id(255),customer_group_id);
");

$installer->endSetup();