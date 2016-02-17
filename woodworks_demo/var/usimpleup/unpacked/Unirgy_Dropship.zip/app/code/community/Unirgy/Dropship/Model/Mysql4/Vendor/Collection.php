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

class Unirgy_Dropship_Model_Mysql4_Vendor_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('udropship/vendor');
        parent::_construct();
    }

    public function addVendorFilter($vendorIds)
    {
        $this->getSelect()->where('vendor_id in (?)', (array)$vendorIds);
        return $this;
    }

    public function addProductFilter($productIds, $priority=null)
    {
        $this->getSelect()->join(
            array('product'=>$this->getTable('vendor_product')),
            'product.vendor_id=main_table.vendor_id',
            array('product_id', 'priority')
        )->where('product.product_id in (?)', (array)$productIds);

        if (!is_null($priority)) {
            //$this->getSelect()->where('product.priority=?', $priority);
        }

        return $this;
    }

    public function addStatusFilter($status)
    {
        $this->getSelect()->where('status in (?)', $status);
        return $this;
    }
}