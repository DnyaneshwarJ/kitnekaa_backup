<?php

class Unirgy_DropshipVacation_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function processVendorChange($vendor)
    {
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $disabled = Unirgy_DropshipVacation_Model_Source::MODE_VACATION_DISABLE;
        if ($vendor->dataHasChangedFor('vacation_mode')) {
            if ($vendor->getData('vacation_mode') == $disabled) {
                $prodStatus = Unirgy_DropshipVendorProduct_Model_ProductStatus::STATUS_VACATION;
                $fromStatus = Mage_Catalog_Model_Product_Status::STATUS_ENABLED;
            } elseif ($vendor->getOrigData('vacation_mode') == $disabled) {
                $prodStatus = Mage_Catalog_Model_Product_Status::STATUS_ENABLED;
                $fromStatus = Unirgy_DropshipVendorProduct_Model_ProductStatus::STATUS_VACATION;
            }
            if (isset($prodStatus) && ($assocPids = $vendor->getResource()->getVendorAttributeProducts($vendor))) {
                $assocPids = array_keys($assocPids);
                $pStAttr = Mage::getSingleton('eav/config')->getAttribute('catalog_product', 'status');
                $pStUpdateSql = sprintf('update %s set value=%s where entity_id in (%s) and attribute_id=%s and value=%s',
                    $pStAttr->getBackendTable(), $write->quote($prodStatus),
                    $write->quote($assocPids), $write->quote($pStAttr->getAttributeId()),
                    $write->quote($fromStatus)
                );
                $write->query($pStUpdateSql);
                if (Mage::helper('udropship')->hasMageFeature('indexer_1.4')) {
                    Mage::getSingleton('index/indexer')->processEntityAction(
                        Mage::getModel('catalog/product_action')->setProductIds($assocPids)->setAttributesData(array('status'=>$prodStatus)),
                        Mage_Catalog_Model_Product::ENTITY, Mage_Index_Model_Event::TYPE_MASS_ACTION
                    );
                }
            }
        }
    }
}
