<?php

class Unirgy_Dropship_Model_Mysql4_Indexer_VendorProductAssoc extends Mage_Index_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_setResource('udropship');
    }

    public function reindexProducts($entityIds=null)
    {
        if (!Mage::app()->isInstalled()) return false;

        if (empty($entityIds)) return false;

        $conn = $this->_getWriteAdapter();

        $vendorAttr = Mage::getSingleton('eav/config')->getAttribute('catalog_product', 'udropship_vendor');

        $conn->delete($this->getTable('udropship/vendor_product_assoc'), array('product_id in (?)' => $entityIds));

        $select = $conn->select()
            ->from(array('vid' => $vendorAttr->getBackend()->getTable()), array())
            ->join(
                array('v'=>$this->getTable('udropship/vendor')),
                'v.vendor_id=vid.value',
                array()
            )
            ->join(
                array('pid'=>$this->getTable('catalog/product')),
                'pid.entity_id=vid.entity_id',
                array()
            )
            ->where('vid.attribute_id=?', $vendorAttr->getId())
            ->where('vid.entity_id in (?)', $entityIds);

        $select->columns(array(
            'vid.value', 'vid.entity_id',
            new Zend_Db_Expr('1'),
            new Zend_Db_Expr('0'),
        ));

        $insertSelect = sprintf("INSERT INTO %s (vendor_id,product_id,is_attribute,is_udmulti) %s "
            ." ON DUPLICATE KEY UPDATE is_attribute=values(is_attribute),is_udmulti=values(is_udmulti)",
            $this->getTable('udropship/vendor_product_assoc'), $select
        );
        $conn->query($insertSelect);

        $select = $conn->select()
            ->from(array('vid' => $this->getTable('udropship/vendor_product')), array())
            ->join(
                array('v'=>$this->getTable('udropship/vendor')),
                'v.vendor_id=vid.vendor_id',
                array()
            )
            ->join(
                array('pid'=>$this->getTable('catalog/product')),
                'pid.entity_id=vid.product_id',
                array()
            )
            ->where('vid.product_id in (?)', $entityIds);

        $select->columns(array(
            'vid.vendor_id', 'vid.product_id',
            new Zend_Db_Expr('0'),
            new Zend_Db_Expr('1'),
        ));

        $insertSelect = sprintf("INSERT INTO %s (vendor_id,product_id,is_attribute,is_udmulti) %s "
            ." ON DUPLICATE KEY UPDATE is_attribute=is_attribute,is_udmulti=values(is_udmulti)",
            $this->getTable('udropship/vendor_product_assoc'), $select
        );
        $conn->query($insertSelect);

        return $this;
    }

    public function reindexVendors($entityIds=null)
    {
        if (!Mage::app()->isInstalled()) return false;

        if (empty($entityIds)) return false;

        $conn = $this->_getWriteAdapter();

        $vendorAttr = Mage::getSingleton('eav/config')->getAttribute('catalog_product', 'udropship_vendor');

        $conn->delete($this->getTable('udropship/vendor_product_assoc'), array('vendor_id in (?)' => $entityIds));

        $select = $conn->select()
            ->from(array('vid' => $vendorAttr->getBackend()->getTable()), array())
            ->join(
                array('v'=>$this->getTable('udropship/vendor')),
                'v.vendor_id=vid.value',
                array()
            )
            ->where('vid.attribute_id=?', $vendorAttr->getId())
            ->where('vid.value in (?)', $entityIds);

        $select->columns(array(
            'vid.value', 'vid.entity_id',
            new Zend_Db_Expr('1'),
            new Zend_Db_Expr('0'),
        ));

        $insertSelect = sprintf("INSERT INTO %s (vendor_id,product_id,is_attribute,is_udmulti) %s "
            ." ON DUPLICATE KEY UPDATE is_attribute=values(is_attribute),is_udmulti=values(is_udmulti)",
            $this->getTable('udropship/vendor_product_assoc'), $select
        );
        $conn->query($insertSelect);

        $select = $conn->select()
            ->from(array('vid' => $this->getTable('udropship/vendor_product')), array())
            ->join(
                array('v'=>$this->getTable('udropship/vendor')),
                'v.vendor_id=vid.vendor_id',
                array()
            )
            ->join(
                array('pid'=>$this->getTable('catalog/product')),
                'pid.entity_id=vid.product_id',
                array()
            )
            ->where('vid.vendor_id in (?)', $entityIds);

        $select->columns(array(
            'vid.vendor_id', 'vid.product_id',
            new Zend_Db_Expr('0'),
            new Zend_Db_Expr('1'),
        ));

        $insertSelect = sprintf("INSERT INTO %s (vendor_id,product_id,is_attribute,is_udmulti) %s "
            ." ON DUPLICATE KEY UPDATE is_attribute=is_attribute,is_udmulti=values(is_udmulti)",
            $this->getTable('udropship/vendor_product_assoc'), $select
        );
        $conn->query($insertSelect);

        return $this;
    }

    public function reindexAll()
    {
        if (!Mage::app()->isInstalled()) return false;
        $conn = $this->_getWriteAdapter();
        $conn->query(sprintf('truncate %s', $this->getTable('udropship/vendor_product_assoc')));
        $vSelect = $conn->select()
            ->from(array('v' => $this->getTable('udropship/vendor')), array('vendor_id'));
        $vIds = $conn->fetchCol($vSelect);
        $this->reindexVendors($vIds);
        return $this;
    }

    public function disableTableKeys()
    {
        return $this;
    }

    public function enableTableKeys()
    {
        return $this;
    }

}