<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    Unirgy_Dropship
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

class Unirgy_Dropship_Model_Mysql4_Vendor_NotifyLowstock_Collection extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
{
    protected $_vendor;
    public function setVendor($vendor)
    {
        $this->_vendor = $vendor;
        return $this;
    }
    public function getVendor()
    {
        return $this->_vendor;
    }
    public function initLowstockSelect($vendor)
    {
        $this->setVendor($vendor);
        $this->_initLowstockSelect();
        $this->addAttributeToFilter('status', array('in'=>array(Mage_Catalog_Model_Product_Status::STATUS_ENABLED)));
        return $this;
    }
    protected function _initLowstockSelect()
    {
        $conn = $this->getResource()->getReadConnection();
        $this
            ->addAttributeToFilter('type_id', 'simple')
            ->addAttributeToSelect(array('sku', 'name'));
        $this->getSelect()->join(
            array('uv' => $this->getTable('udropship/vendor')), 
            $conn->quoteInto('vendor_id=?', $this->getVendor()->getId()), 
            array('notify_lowstock_qty')
        );
        $this->getSelect()->joinLeft(
            array('uvls' => $this->getTable('udropship/vendor_lowstock')),
            'uvls.product_id=e.entity_id and uv.vendor_id=uvls.vendor_id',
            array('notified'=>'notified')
        );
        $this->getSelect()->where('uvls.notified IS NULL OR uvls.notified!=1');
        $this->_addAttributeJoin('udropship_vendor', 'left');
        $this->getSelect()->join(
            array('cisi' => $this->getTable('cataloginventory/stock_item')), 
            $conn->quoteInto('cisi.product_id=e.entity_id AND cisi.stock_id=?',Mage_CatalogInventory_Model_Stock::DEFAULT_STOCK_ID), 
            array('stock_status'=>$this->_getStockField('status'))
        );
        $this->getSelect()->joinLeft(
            array('uvp' => $this->getTable('udropship/vendor_product')), 
            $conn->quoteInto('uvp.product_id=e.entity_id AND uvp.vendor_id=?', $this->getVendor()->getId()), 
            array('vendor_cost'=>'vendor_cost')
        );
        $vsAttr = Mage::getStoreConfig('udropship/vendor/vendor_sku_attribute');
        if (!Mage::helper('udropship')->isUdmultiAvailable()) {
            if ($vsAttr && $vsAttr!='sku' && Mage::helper('udropship')->checkProductAttribute($vsAttr)) {
                $this->addAttributeToSelect(array($vsAttr));
            }
        } else {
            $this->getSelect()->columns(array('vendor_sku'=>'uvp.vendor_sku'));
        }
        $this->getSelect()->columns(array('stock_qty'=>$this->_getStockField('qty')));
        
        if (!$this->_isLocalVendor() || !$this->_isLocalStock()) {
            $this->addAttributeToFilter('entity_id', array('in'=>$this->getVendor()->getAssociatedProductIds()));
        }
        if (!$this->_isLocalVendor() && $this->_isLocalStock()) {
            $this->getSelect()->where($conn->quoteInto(
                sprintf('%1$s is NULL OR %1$s!=?', $this->_getAttributeFieldName('udropship_vendor')),
                $this->getVendor()->getId()
            ));
        }
        $this->getSelect()
            ->where(sprintf(
            	'uvp.vendor_product_id is not null'
                .' AND ('
            	." uvp.stock_qty is not null AND uvp.stock_qty<=uv.notify_lowstock_qty"
            	." OR uv.vendor_id=%1\$s AND uvp.stock_qty is null AND cisi.qty<=uv.notify_lowstock_qty AND (%2\$s is NULL OR %2\$s!=%1\$s OR %3\$s)"
            	.') OR uvp.vendor_product_id is null'
                ." AND cisi.qty<=uv.notify_lowstock_qty", 
                Mage::helper('udropship')->getLocalVendorId(), $this->_getAttributeFieldName('udropship_vendor'), $this->_isLocalStock()
             ))
         ;
         return $this;
    }
    
    public function markLowstockNotified()
    {
        $conn = $this->getResource()->getReadConnection();
        $select = clone $this->getSelect();
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->columns($columns = array(
            'vendor_id' => new Zend_Db_Expr($this->getVendor()->getId()),
            'product_id' => $this->_getAttributeFieldName('entity_id'),
            'notified_at' => new Zend_Db_Expr($conn->quote(now())),
            'notified' => new Zend_Db_Expr(1)
        ));
        Mage::getResourceSingleton('udropship/vendor_notifyLowstock')->markLowstockNotified($select, $columns);
    }
    
    protected function _isLocalStock()
    {
        return intval(Mage::getStoreConfig('udropship/stock/availability')=='local_if_in_stock');
    }
    
    protected function _isLocalVendor()
    {
        return intval($this->getVendor()->getId()==Mage::getStoreConfig('udropship/vendor/local_vendor'));
    }
    
    protected function _getStockField($type)
    {
        if (Mage::helper('udropship')->isUdmultiAvailable()) {
            switch ($type) {
                case 'qty':
                    return new Zend_Db_Expr('IF(uvp.vendor_product_id is null or ('.$this->_isLocalVendor().' and uvp.stock_qty is null), cisi.qty, uvp.stock_qty)');
                case 'status':
                    return new Zend_Db_Expr('IF(uvp.vendor_product_id is null or '.$this->_isLocalVendor().', cisi.is_in_stock, null)');
            }
        } else {
            switch ($type) {
                case 'qty':
                    return 'cisi.qty';
                case 'status':
                    return 'cisi.is_in_stock';
            }
        }
    }
}