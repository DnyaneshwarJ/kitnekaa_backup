<?php

class Unirgy_Dropship_Block_Adminhtml_Batch_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('batchGrid');
        $this->setDefaultSort('batch_id');
        $this->setDefaultDir('desc');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('udropship/label_batch')->getCollection();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $baseUrl = $this->getUrl();

        $this->addColumn('batch_id', array(
            'header'    => Mage::helper('udropship')->__('ID'),
            'index'     => 'batch_id',
            'width'     => 10,
            'type'      => 'number',

        ));

        $this->addColumn('created_at', array(
            'header'    => Mage::helper('udropship')->__('Created At'),
            'index'     => 'created_at',
            'type'      => 'datetime',
            'width'     => 150,
        ));

        $this->addColumn('title', array(
            'header'    => Mage::helper('udropship')->__('Title'),
            'index'     => 'title',
        ));

        $this->addColumn('vendor_id', array(
            'header' => Mage::helper('udropship')->__('Vendor'),
            'index' => 'vendor_id',
            'type' => 'options',
            'options' => Mage::getSingleton('udropship/source')->setPath('vendors')->toOptionHash(),
        ));

        $this->addColumn('label_type', array(
            'header' => Mage::helper('udropship')->__('Label Type'),
            'index' => 'label_type',
            'type' => 'options',
            'options' => Mage::getSingleton('udropship/source')->setPath('label_type')->toOptionHash(),
        ));

        $this->addColumn('shipment_cnt', array(
            'header'    => Mage::helper('udropship')->__('# of Shipments'),
            'index'     => 'shipment_cnt',
            'type'      => 'number',
        ));

        $this->addColumn('page_actions', array(
            'header'    => Mage::helper('udropship')->__('Action'),
            'width'     => 150,
            'sortable'  => false,
            'filter'    => false,
            'renderer'  => 'udropship/adminhtml_batch_action',
        ));

        return parent::_prepareColumns();
    }
}