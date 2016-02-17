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

class Unirgy_Dropship_Block_Adminhtml_ReportItem_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    protected $_useBaseCostColumn = false;
    public function __construct()
    {
        parent::__construct();
        $this->setId('reportItemGrid');
        $this->setDefaultSort('order_created_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $res  = Mage::getSingleton('core/resource');
        $conn = $res->getConnection('core_read');
        $this->_useBaseCostColumn = $conn->tableColumnExists($res->getTableName('sales/order_item'), 'base_cost');
    }

    public function t($table)
    {
        return Mage::getSingleton('core/resource')->getTableName($table);
    }

    protected function _getFlatExpressionColumn($key, $bypass=true)
    {
        $result = $bypass ? $key : null;
        switch ($key) {
            case 'tax_amount':
                $result = new Zend_Db_Expr("main_table.qty*((oi.base_tax_amount)/(if(oi.qty_ordered!=0&&oi.qty_ordered is not null, oi.qty_ordered, 1)))");
                break;
            case 'po_row_total':
                $result = new Zend_Db_Expr("main_table.qty*((oi.base_row_total+oi.base_tax_amount)/(if(oi.qty_ordered!=0&&oi.qty_ordered is not null, oi.qty_ordered, 1)))");
                break;
        }
        return $result;
    }

    protected function _prepareCollection()
    {
        if (Mage::helper('udropship')->isSalesFlat()) {

            $res = Mage::getSingleton('core/resource');

            $collection = Mage::getResourceModel('sales/order_shipment_item_collection');
            $collection->getSelect()
            	->join(array('oi'=>$res->getTableName('sales/order_item')), 'oi.item_id=main_table.order_item_id', array('discount_amount'=>'oi.base_discount_amount', 'cost'=>($this->_useBaseCostColumn ? 'oi.base_cost' : 'oi.cost'), 'tax_amount'=>$this->_getFlatExpressionColumn('tax_amount'),'base_price','po_row_total'=>$this->_getFlatExpressionColumn('po_row_total')))
                ->join(array('t'=>$res->getTableName('sales/shipment')), 't.entity_id=main_table.parent_id', array('udropship_vendor', 'udropship_available_at', 'udropship_method', 'udropship_method_description', 'udropship_status', 'base_shipping_amount', 'po_increment_id'=>'increment_id', 'created_at'))
                ->join(array('o'=>$res->getTableName('sales/order')), 'o.entity_id=oi.order_id', array('order_status'=>'o.status', 'order_increment_id'=>'o.increment_id', 'order_created_at'=>'o.created_at'))
			;
        } else {

            $collection = Mage::getResourceModel('sales/order_shipment_item_collection')
                ->addAttributeToSelect('sku')
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('price')
                ->addAttributeToSelect('qty')
                ->addAttributeToSelect('order_item_id', 'inner')
                ->joinTable('sales/order_item', 'item_id=order_item_id', array('discount_amount'=>'base_discount_amount', 'cost'=>$this->_useBaseCostColumn ? 'base_cost' : 'cost', 'tax_amount'=>'base_tax_amount'))
                ->joinAttribute('order_id', 'shipment/order_id', 'parent_id')
                ->joinAttribute('udropship_status', 'shipment/udropship_status', 'parent_id')
                ->joinAttribute('udropship_vendor', 'shipment/udropship_vendor', 'parent_id')
                ->joinAttribute('shipping_amount', 'shipment/shipping_amount', 'parent_id')
                ->joinAttribute('po_increment_id', 'shipment/increment_id', 'parent_id')
                ->joinAttribute('created_at', 'shipment/created_at', 'parent_id')
                ->joinAttribute('order_increment_id', 'order/increment_id', 'order_id')
                ->joinAttribute('order_created_at', 'order/created_at', 'order_id')
                ->joinAttribute('order_status', 'order/status', 'order_id')
                ->joinAttribute('base_currency_code', 'order/base_currency_code', 'order_id', null, 'left')
            ;
        }

        $collection->getSelect()->where(
        	(!Mage::helper('udropship')->isSalesFlat() ? $this->t('sales/order_item') : 'oi').'.parent_item_id is null'
        );
        
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $flat = Mage::helper('udropship')->isSalesFlat();
        
        $hlp = Mage::helper('udropship');
        
        $this->addColumn('order_increment_id', array(
            'header'    => Mage::helper('udropship')->__('Order #'),
            'index'     => 'order_increment_id',
        	'filter_index' => !$flat ? null : 'o.increment_id',
            'type'      => 'number',
        ));

        $this->addColumn('order_created_at', array(
            'header'    => Mage::helper('udropship')->__('Order Date'),
            'index'     => 'order_created_at',
        	'filter_index' => !$flat ? null : 'o.created_at',
            'type'      => 'datetime',
        ));
        
        $this->addColumn('order_status', array(
            'header'    => Mage::helper('udropship')->__('Order Status'),
            'index'     => 'order_status',
            'filter_index' => !$flat ? null : 'o.status',
            'type' => 'options',
            'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
        ));
        
        $this->addColumn('po_increment_id', array(
            'header'    => Mage::helper('udropship')->__('PO #'),
            'index'     => 'po_increment_id',
            'filter_index' => !$flat ? null : 't.increment_id',
            'type'      => 'text',
        ));

        $this->addColumn('created_at', array(
            'header'    => Mage::helper('udropship')->__('PO Date'),
            'index'     => 'created_at',
            'filter_index' => !$flat ? null : 't.created_at',
            'type'      => 'datetime',
        ));
        
        $this->addColumn('udropship_status', array(
            'header' => Mage::helper('udropship')->__('PO Status'),
            'index' => 'udropship_status',
            'filter_index' => !$flat ? null : 't.udropship_status',
            'type' => 'options',
            'options' => Mage::getSingleton('udropship/source')->setPath('shipment_statuses')->toOptionHash(),
        ));

        /*
        $this->addColumn('base_shipping_amount', array(
            'header' => Mage::helper('udropship')->__('PO Shipping Price'),
            'index' => 'base_shipping_amount',
            'filter_index' => !$flat ? null : 't.base_shipping_amount',
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base'),
        ));
        */
        
        $this->addColumn('udropship_vendor', array(
            'header' => Mage::helper('udropship')->__('Vendor'),
            'index' => 'udropship_vendor',
        	'filter_index' => !$flat ? null : 't.udropship_vendor',
            'type' => 'options',
            'options' => Mage::getSingleton('udropship/source')->setPath('vendors')->toOptionHash(),
            'filter' => 'udropship/vendor_gridColumnFilter'
        ));
        
        $this->addColumn('sku', array(
            'header' => Mage::helper('udropship')->__('PO Item SKU'),
            'index' => 'sku',
        	'filter_index' => !$flat ? null : 'main_table.sku',
        ));
        
        $this->addColumn('name', array(
            'header' => Mage::helper('udropship')->__('PO Item Name'),
            'index' => 'name',
        	'filter_index' => !$flat ? null : 'main_table.name',
        ));
        
        $this->addColumn('base_price', array(
            'header' => Mage::helper('udropship')->__('PO Item Price'),
            'index' => 'base_price',
        	'filter_index' => !$flat ? null : 'main_table.base_price',
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base'),
        ));
        
        $this->addColumn('discount_amount', array(
            'header' => Mage::helper('udropship')->__('PO Item Discount'),
            'index' => 'discount_amount',
        	'filter_index' => !$flat ? null : 'oi.base_discount_amount',
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base'),
        ));
        
        $this->addColumn('cost', array(
            'header' => Mage::helper('udropship')->__('PO Item Cost'),
            'index' => 'cost',
        	'filter_index' => !$flat ? null : 'oi.base_cost',
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base'),
        ));
        
        $this->addColumn('qty', array(
            'header'    => Mage::helper('udropship')->__('PO Item Qty'),
            'index'     => 'qty',
            'type'      => 'number',
        ));

        $this->addColumn('tax_amount', array(
            'header' => Mage::helper('udropship')->__('PO Item Tax'),
            'index' => 'tax_amount',
        	'filter_index' => !$flat ? null : $this->_getFlatExpressionColumn('tax_amount'),
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base'),
        ));

        $this->addColumn('po_row_total', array(
            'header' => Mage::helper('udropship')->__('PO Item Row Total'),
            'index' => 'po_row_total',
            'filter_index' => !$flat ? null : $this->_getFlatExpressionColumn('po_row_total'),
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base'),
        ));
        
        $this->addExportType('*/*/itemExportCsv', Mage::helper('udropship')->__('CSV'));
        $this->addExportType('*/*/itemExportXml', Mage::helper('udropship')->__('XML'));
        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/itemGrid', array('_current'=>true));
    }
}
