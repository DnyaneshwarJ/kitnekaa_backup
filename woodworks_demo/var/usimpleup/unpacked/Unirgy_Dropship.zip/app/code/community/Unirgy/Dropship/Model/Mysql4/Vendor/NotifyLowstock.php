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

class Unirgy_Dropship_Model_Mysql4_Vendor_NotifyLowstock extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('udropship/vendor_lowstock', 'id');
    }
    
    public function vendorCleanLowstock()
    {
        $conn = $this->getReadConnection();
        $idsToDel = $conn->fetchCol($conn->select()
                ->from(array('vls' => $this->getTable('udropship/vendor_lowstock')), array('id'))
                ->join(array('uv' => $this->getTable('udropship/vendor')), 'vls.vendor_id=uv.vendor_id', array())
                ->join(array('cisi' => $this->getTable('cataloginventory/stock_item')), 
                    $conn->quoteInto('cisi.product_id=vls.product_id AND cisi.stock_id=?',Mage_CatalogInventory_Model_Stock::DEFAULT_STOCK_ID), array())
                ->joinLeft(array('uvp' => $this->getTable('udropship/vendor_product')), 'uvp.vendor_id=vls.vendor_id and uvp.product_id=vls.product_id', array())
                ->where("uvp.vendor_product_id is not null AND (uvp.stock_qty is not null AND uvp.stock_qty>uv.notify_lowstock_qty"
                	." OR uvp.stock_qty is null AND uv.vendor_id!=?)", Mage::helper('udropship')->getLocalVendorId())
                ->orWhere("uvp.vendor_product_id is not null AND uvp.stock_qty is null"
                    ." AND uv.vendor_id=? AND cisi.qty>uv.notify_lowstock_qty", Mage::helper('udropship')->getLocalVendorId())
                ->orWhere("uvp.vendor_product_id is null AND cisi.qty>uv.notify_lowstock_qty")
        );
        $conn->delete($this->getTable('udropship/vendor_lowstock'), $conn->quoteInto('id in (?)', $idsToDel));
    }
    
    public function markLowstockNotified($select, $columns)
    {
        $this->_getWriteAdapter()->query(sprintf(
            'INSERT INTO %s (%s) %s %s',
            $this->getTable('udropship/vendor_lowstock'), implode(',', array_keys($columns)), $select,
            Mage::helper('udropship')->createOnDuplicateExpr($this->_getWriteAdapter(), array_keys($columns))
        ));
    }
}