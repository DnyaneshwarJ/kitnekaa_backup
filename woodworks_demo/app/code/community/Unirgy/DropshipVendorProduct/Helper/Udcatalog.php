<?php

class Unirgy_DropshipVendorProduct_Helper_Udcatalog extends Unirgy_Dropship_Helper_Catalog
{
    public function getIdentifyImageAttributes($prod, $isNew)
    {
        return Mage::helper('udprod')->getIdentifyImageAttributes($prod, $isNew);
    }
    public function createCfgAttr($cfgProd, $cfgAttrId, $pos)
    {
        $cfgPid = $cfgProd;
        $identifyImage = 0;
        if ($cfgProd instanceof Mage_Catalog_Model_Product) {
            $cfgPid = $cfgProd->getId();
            $identifyImage = 0;
            foreach ($this->getIdentifyImageAttributes($cfgProd, true) as $cfgAttr) {
                if ($cfgAttr->getId() == $cfgAttrId) {
                    $identifyImage = 1;
                    break;
                }
            }
        }
        $res = Mage::getSingleton('core/resource');
        $write = $res->getConnection('catalog_write');
        $superAttrTable = $res->getTableName('catalog/product_super_attribute');
        $superLabelTable = $res->getTableName('catalog/product_super_attribute_label');

        $exists = $write->fetchRow("select sa.*, sal.value_id, sal.value label from {$superAttrTable} sa
            inner join {$superLabelTable} sal on sal.product_super_attribute_id=sa.product_super_attribute_id
            where sa.product_id={$cfgPid} and sa.attribute_id={$cfgAttrId} and sal.store_id=0");
        if (!$exists) {
            $write->insert($superAttrTable, array(
                'product_id' => $cfgPid,
                'attribute_id' => $cfgAttrId,
                'position' => $pos,
                'identify_image' => $identifyImage
            ));
            $saId = $write->lastInsertId($superAttrTable);
            $write->insert($superLabelTable, array(
                'product_super_attribute_id' => $saId,
                'store_id' => 0,
                'use_default' => 1,
                'value' => '',
            ));
        }

        return $this;
    }
}