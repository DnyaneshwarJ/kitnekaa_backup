<?php

class Unirgy_Dropship_Model_Mysql4_ProductHelper extends Mage_Catalog_Model_Resource_Eav_Mysql4_Abstract
{

    protected function _construct()
    {
        $resource = Mage::getSingleton('core/resource');
        $this->setType(Mage_Catalog_Model_Product::ENTITY)
            ->setConnection(
                $resource->getConnection('catalog_read'),
                $resource->getConnection('catalog_write')
            );
    }

    public function multiUpdateAttributes($attrData, $storeId)
    {
        $object = new Varien_Object();
        $object->setIdFieldName('entity_id')
            ->setStoreId($storeId);

        $this->_getWriteAdapter()->beginTransaction();
        try {
            $i = 0;
            foreach ($attrData as $entityId => $_attrData) {
                foreach ($_attrData as $attrCode => $value) {
                    $attribute = $this->getAttribute($attrCode);
                    if (!$attribute->getAttributeId()) {
                        continue;
                    }
                    $i++;
                    $object->setId($entityId);
                    $this->_saveAttributeValue($object, $attribute, $value);
                    if ($i % 1000 == 0) {
                        $this->_processAttributeValues();
                    }
                    $this->_processAttributeValues();
                }
            }
            $this->_getWriteAdapter()->commit();
        } catch (Exception $e) {
            $this->_getWriteAdapter()->rollBack();
            throw $e;
        }

        return $this;
    }
}
