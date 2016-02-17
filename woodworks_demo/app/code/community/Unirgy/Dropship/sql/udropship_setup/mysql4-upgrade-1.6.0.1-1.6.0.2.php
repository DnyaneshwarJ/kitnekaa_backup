<?php

$this->startSetup();

$eav = new Mage_Eav_Model_Entity_Setup('sales_setup');
$config = new Mage_Eav_Model_Config();

$eav->addAttribute('shipment_track', 'next_check', array('type' => 'datetime'));
$eav->addAttribute('shipment_track', 'udropship_status', array('type' => 'varchar'));

/*
$trackStatusTable = $this->getTable('sales_order_entity_varchar');
$trackStatusAttrId = $config->getAttribute('shipment_track', 'udropship_status')->getId();
$nextCheckTable = $this->getTable('sales_order_entity_datetime');
$nextCheckAttrId = $config->getAttribute('shipment_track', 'next_check')->getId();

$this->run("
INSERT INTO `{$nextCheckTable}` (entity_type_id, attribute_id, entity_id, `value`)
SELECT v.entity_type_id, '{$nextCheckAttrId}', v.entity_id, '0000-00-00'
FROM `{$trackStatusTable}` v
LEFT JOIN `{$nextCheckTable}` d ON d.entity_id=v.entity_id AND d.attribute_id='{$nextCheckAttrId}'
WHERE v.attribute_id='{$trackStatusAttrId}' AND v.value='S' AND d.value IS NULL;
");
*/

$this->endSetup();