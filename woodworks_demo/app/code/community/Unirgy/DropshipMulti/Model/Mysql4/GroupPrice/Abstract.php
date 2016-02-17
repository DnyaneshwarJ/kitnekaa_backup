<?php

class Unirgy_DropshipMulti_Model_Mysql4_GroupPrice_Abstract extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function joinMultiVendorData($isActive=false, $joinVendors=false, $columns=array())
    {
        if (!$this->getFlag('udropship_vendor_joined')) {
            $this->join(
                array('mvd'=>'udropship/vendor_product'),
                'main_table.product_id=mvd.product_id and main_table.vendor_id=mvd.vendor_id',
                $columns
            );
            if ($isActive) {
                $this->getSelect()->where('mvd.status>0');
            }
            if ($joinVendors || $isActive) {
                $res = Mage::getSingleton('core/resource');
                $this->getSelect()
                    ->join(
                        array('v'=>$res->getTableName('udropship_vendor')),
                        'v.vendor_id=main_table.vendor_id',
                        $joinVendors ? '*' : array()
                    );
                if ($isActive) {
                    $this->getSelect()->where("v.status='A'");
                }
            }
            $this->setFlag('udropship_vendor_joined',1);
        }
        return $this;
    }

    public function addProductFilter($productIds, $priority=null)
    {
        $this->getSelect()->where('main_table.product_id in (?)', (array)$productIds);
        return $this;
    }

    public function addVendorFilter($vendorIds)
    {
        $this->getSelect()->where('main_table.vendor_id in (?)', (array)$vendorIds);
        return $this;
    }
    protected function _afterLoad()
    {
        parent::_afterLoad();
        foreach ($this->_items as $item) {
            if ($item->getAllGroups()) {
                $item->setCustomerGroupId(Mage_Customer_Model_Group::CUST_GROUP_ALL);
            }
            $item->setData('website_price', $item->getData('value'));
            $item->setData('price', $item->getData('value'));
            $item->setData('price_qty', $item->getData('qty'));
            $item->setData('cust_group', $item->getData('customer_group_id'));
        }
        return $this;
    }
    public function join($table, $cond, $cols = '*')
    {
        if (is_array($table)) {
            foreach ($table as $k => $v) {
                $alias = $k;
                $table = $v;
                break;
            }
        } else {
            $alias = $table;
        }

        if (!isset($this->_joinedTables[$table])) {
            $this->getSelect()->join(
                array($alias => $this->getTable($table)),
                $cond,
                $cols
            );
            $this->_joinedTables[$alias] = true;
        }
        return $this;
    }
}