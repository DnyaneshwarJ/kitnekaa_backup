<?php

class Unirgy_Dropship_Model_Mysql4_Po_Collection extends Mage_Sales_Model_Mysql4_Order_Shipment_Collection
{
    protected function _construct()
    {
        $this->_init('udropship/po');
    }

    public function addPendingBatchStatusFilter()
    {
        return $this->_addPendingBatchStatusFilter();
    }
    public function addPendingBatchStatusVendorFilter($vendor)
    {
        return $this->_addPendingBatchStatusFilter($vendor);
    }
    protected function _addPendingBatchStatusFilter($vendor=null)
    {
        if (is_array($vendor)) {
            $exportOnPoStatusAll = array();
            foreach ($vendor as $vId) {
                $exportOnPoStatus = array();
                if ($vId && ($v = Mage::helper('udropship')->getVendor($vId)) && $v->getId()) {
                    $exportOnPoStatus = $v->getData('batch_export_orders_export_on_po_status');
                }
                if (in_array('999', $exportOnPoStatus) || empty($exportOnPoStatus)) {
                    $exportOnPoStatus = Mage::getStoreConfig('udropship/batch/export_on_po_status');
                    if (!is_array($exportOnPoStatus)) {
                        $exportOnPoStatus = explode(',', $exportOnPoStatus);
                    }
                }
                $exportOnPoStatusAll[(int)$vId] = $this->getSelect()->getAdapter()->quoteInto('udropship_status in (?)', $exportOnPoStatus);
            }
        } else {
            $exportOnPoStatus = array();
            if ($vendor && ($vendor = Mage::helper('udropship')->getVendor($vendor)) && $vendor->getId()) {
                $exportOnPoStatus = $vendor->getData('batch_export_orders_export_on_po_status');
            }
            if (in_array('999', $exportOnPoStatus) || empty($exportOnPoStatus)) {
                $exportOnPoStatus = Mage::getStoreConfig('udropship/batch/export_on_po_status');
                if (!is_array($exportOnPoStatus)) {
                    $exportOnPoStatus = explode(',', $exportOnPoStatus);
                }
            }
        }
        if (!Mage::helper('udropship')->isSalesFlat()) {
            $attr = Mage::getSingleton('eav/config')->getAttribute('shipment', 'udropship_status');
            $this->getSelect()->joinLeft(
                array('_udbatch_status'=>$attr->getBackend()->getTable()),
                "_udbatch_status.entity_id=e.entity_id and _udbatch_status.attribute_id={$attr->getId()}",
                array()
            )->where("_udbatch_status.value in (?)", $exportOnPoStatus);
        } else {
            if (is_array($vendor) && isset($exportOnPoStatusAll)) {
                $this->getSelect()->where(
                    Mage::helper('udropship/catalog')->getCaseSql('udropship_vendor', $exportOnPoStatusAll)
                );
            } else {
                $this->getSelect()->where("udropship_status in (?)", $exportOnPoStatus);
            }
        }
        return $this;
    }

    public function addOrders()
    {
        if (!Mage::helper('udropship')->isSalesFlat()) {
            $this->addAttributeToSelect('order_id', 'inner');
        }

        $orderIds = array();
        foreach ($this as $po) {
            if ($po->getOrderId()) {
                $orderIds[$po->getOrderId()] = 1;
            }
        }

        if ($orderIds) {
            $orders = Mage::getModel('sales/order')->getCollection()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('entity_id', array('in'=>array_keys($orderIds)));
            foreach ($this as $po) {
                $po->setOrder($orders->getItemById($po->getOrderId()));
            }
        }
        return $this;
    }
}