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

class Unirgy_Dropship_Block_Adminhtml_Vendor_Edit_Tab_Products extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('udropship_vendor_products');
        $this->setDefaultSort('entity_id');
        $this->setUseAjax(true);
    }

    public function getVendor()
    {
        $vendor = Mage::registry('vendor_data');
        if (!$vendor) {
            $vendor = Mage::getModel('udropship/vendor')->load($this->getVendorId());
            Mage::register('vendor_data', $vendor);
        }
        return $vendor;
    }

    protected function _addColumnFilterToCollection($column)
    {
        $id = $column->getId();
        $value = $column->getFilter()->getValue();
        $select = $this->getCollection()->getSelect();
        if (Mage::helper('udropship')->isUdmultiAvailable()) {
            switch ($id) {
            case 'vendor_sku':
                if (!is_null($value) && $value!=='') {
                    $select->where('vendor_sku like ?', $column->getFilter()->getValue().'%');
                }
                return $this;
    
            case 'vendor_cost':
                if (!is_null($value['from']) && $value['from']!=='') {
                    $select->where($id.'>=?', $value['from']);
                }
                if (!is_null($value['to']) && $value['to']!=='') {
                    $select->where($id.'<=?', $value['to']);
                }
                return $this;

            case 'backorders':
                $select->where($id.'=?', $column->getFilter()->getValue());
                return $this;

            case 'shipping_price':
                if (!is_null($value['from']) && $value['from']!=='') {
                    $select->where($id.'>=?', $value['from']);
                }
                if (!is_null($value['to']) && $value['to']!=='') {
                    $select->where($id.'<=?', $value['to']);
                }
                return $this;
            }
        }
        if (Mage::helper('udropship')->isUdmultiPriceAvailable()) {
            switch ($id) {
            case 'state':
                if (!is_null($value) && $value!=='') {
                    $select->where('state=?', $column->getFilter()->getValue());
                }
                return $this;

            case 'vendor_price':
                if (!is_null($value['from']) && $value['from']!=='') {
                    $select->where($id.'>=?', $value['from']);
                }
                if (!is_null($value['to']) && $value['to']!=='') {
                    $select->where($id.'<=?', $value['to']);
                }
                return $this;
            }
        }
        switch ($id) {
        case 'stock_qty':
            if (!is_null($value['from']) && $value['from']!=='') {
                $select->where($this->_getStockField('qty').'>=?', $value['from']);
            }
            if (!is_null($value['to']) && $value['to']!=='') {
                $select->where($this->_getStockField('qty').'<=?', $value['to']);
            }
            return $this;
        }
        // Set custom filter for in category flag
        if ($column->getId() == 'in_vendor') {
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', array('in'=>$productIds));
            }
            elseif(!empty($productIds)) {
                $this->getCollection()->addFieldToFilter('entity_id', array('nin'=>$productIds));
            }
        }
        else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }
    
    protected function _getStockField($type)
    {
        $v = $this->getVendor();
        if (!$v || !$v->getId()) {
            $isLocalVendor = 0;
        } else {
            $isLocalVendor = intval($v->getId()==Mage::getStoreConfig('udropship/vendor/local_vendor'));
        }
        if (Mage::helper('udropship')->isUdmultiAvailable()) {
            switch ($type) {
                case 'qty':
                    return new Zend_Db_Expr('IF(uvp.vendor_product_id is null, cisi.qty, uvp.stock_qty)');
                case 'status':
                    return new Zend_Db_Expr("IF(uvp.vendor_product_id is null or $isLocalVendor, cisi.is_in_stock, null)");
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

    protected function _prepareCollection()
    {
        if ($this->getVendor()->getId()) {
            $this->setDefaultFilter(array('in_vendor'=>1));
        }
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('price')
            ->addStoreFilter($this->getRequest()->getParam('store'))
//            ->addAttributeToFilter('udropship_vendor', $this->getVendor()->getId());
//            ->addAttributeToFilter('type_id', array('in'=>array('simple')))
        ;
        
        $res = Mage::getSingleton('core/resource');
        $stockTable = $res->getTableName('cataloginventory/stock_item');
        $conn = $collection->getConnection();
        
        $collection->getSelect()->join(
            array('cisi' => $stockTable), 
            $conn->quoteInto('cisi.product_id=e.entity_id AND cisi.stock_id=?', Mage_CatalogInventory_Model_Stock::DEFAULT_STOCK_ID), 
            array('_stock_status'=>$this->_getStockField('status'))
        );
        
        if (Mage::helper('udropship')->isUdmultiAvailable()) {
            $collection->getSelect()->joinLeft(
                array('uvp' => $res->getTableName('udropship/vendor_product')), 
                $conn->quoteInto('uvp.product_id=e.entity_id AND uvp.vendor_id=?', $this->getVendor()->getId()), 
                array('*','_stock_qty'=>$this->_getStockField('qty'), 'vendor_sku'=>'uvp.vendor_sku', 'vendor_cost'=>'uvp.vendor_cost', 'backorders'=>'uvp.backorders')
            );
            $collection->getSelect()->columns(array('_stock_qty'=>$this->_getStockField('qty')));
            //$collection->getSelect()->columns(array('_stock_qty'=>'IFNULL(uvp.stock_qty,cisi.qty'));
        } else {
            $collection->getSelect()->columns(array('stock_qty'=>$this->_getStockField('qty')));
        }

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('in_vendor', array(
            'header_css_class' => 'a-center',
            'type'      => 'checkbox',
            'name'      => 'in_vendor',
            'values'    => $this->_getSelectedProducts(),
            'align'     => 'center',
            'index'     => 'entity_id'
        ));
        $this->addColumn('entity_id', array(
            'header'    => Mage::helper('udropship')->__('ID'),
            'sortable'  => true,
            'width'     => '60',
            'index'     => 'entity_id'
        ));
        $this->addColumn('name', array(
            'header'    => Mage::helper('udropship')->__('Name'),
            'index'     => 'name'
        ));
        $this->addColumn('sku', array(
            'header'    => Mage::helper('udropship')->__('SKU'),
            'width'     => '80',
            'index'     => 'sku'
        ));
        if (Mage::helper('udropship')->isUdmultiAvailable()) {
            $this->addColumn('_vendor_sku', array(
                'header'    => Mage::helper('udropship')->__('Vendor SKU'),
                'index'     => 'vendor_sku',
                'editable'  => true, 'edit_only'=>true,
                'sortable'  => false,
                'filter'    => false,
                'width'     => '100',
            ));
        }
        $this->addColumn('price', array(
            'header'    => Mage::helper('udropship')->__('Price'),
            'type'  => 'currency',
            'currency_code' => (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
            'index'     => 'price'
        ));
        if (Mage::helper('udropship')->isUdmultiAvailable()) {
            $this->addColumn('_vendor_cost', array(
                'header'    => Mage::helper('udropship')->__('Vendor Cost'),
                'type'      => 'number',
                'index'     => 'vendor_cost',
                'editable'  => true, 'edit_only'=>true,
                'sortable'  => false,
                'filter'    => false,
            ));
        if (Mage::helper('udmulti')->isVendorProductShipping()) {
            $this->addColumn('_shipping_price', array(
                'header'    => Mage::helper('udropship')->__('Shipping Price'),
                'type'      => 'number',
                'index'     => 'shipping_price',
                'editable'  => true, 'edit_only'  => true,
                'sortable'  => false,
                'filter'    => false,
            ));
            }
            $this->addColumn('_status', array(
                'header'    => Mage::helper('udropship')->__('Status'),
                'type'      => 'select',
                'index'     => 'status',
                'editable'  => true, 'edit_only'  => true,
                'sortable'  => false,
                'filter'    => false,
                'options'   => Mage::getSingleton('udmulti/source')->setPath('vendor_product_status')->toOptionHash()
            ));
        }
        if (Mage::helper('udropship')->isUdmultiPriceAvailable()) {
            $this->addColumn('_vendor_price', array(
                'header'    => Mage::helper('udropship')->__('Vendor Price'),
                'type'      => 'number',
                'index'     => 'vendor_price',
                'editable'  => true, 'edit_only'  => true,
                'sortable'  => false,
                'filter'    => false,
            ));
            $this->addColumn('_vendor_title', array(
                'header'    => Mage::helper('udropship')->__('Vendor Title'),
                'index'     => 'vendor_title',
                'editable'  => true, 'edit_only'  => true,
                'sortable'  => false,
                'filter'    => false,
                'width'     => '200',
            ));
            $this->addColumn('_state', array(
                'header'    => Mage::helper('udropship')->__('State/Condition'),
                'type'      => 'select',
                'index'     => 'state',
                'editable'  => true, 'edit_only'  => true,
                'sortable'  => false,
                'filter'    => false,
                'options'   => Mage::getSingleton('udmultiprice/source')->setPath('vendor_product_state')->toOptionHash()
            ));

        }
        if (Mage::helper('udropship')->isUdmultiAvailable()) {
            $this->addColumn('_backorders', array(
                'header'    => Mage::helper('udropship')->__('Backorders'),
                'type'      => 'select',
                'index'     => 'backorders',
                'editable'  => true, 'edit_only'=>true,
                'sortable'  => false,
                'filter'    => false,
                'options'   => Mage::getSingleton('udmulti/source')->setPath('backorders')->toOptionHash()
            ));
        }
        $this->addColumn('_stock_qty', array(
            'header'    => Mage::helper('udropship')->__('Vendor Stock Qty'),
            'type'      => 'number',
            'index'     => 'stock_qty',
            'renderer'  => 'udropship/adminhtml_vendor_helper_gridRenderer_stockQty',
            'editable'  => true, 'edit_only'  => true,
            'sortable'  => false,
            'filter'    => false,
            'urq_id'    => '_use_reserved_qty'
        ));
        if ($this->getVendor()->getBackorderByAvailability()) {
        $this->addColumn('_avail_state', array(
            'header'    => Mage::helper('udropship')->__('Availability State'),
            'type'      => 'select',
            'index'     => 'avail_state',
            'editable'  => true, 'edit_only'  => true,
            'sortable'  => false,
            'filter'    => false,
            'options'   => Mage::getSingleton('udmulti/source')->setPath('avail_state')->toOptionHash()
        ));
        $this->addColumn('_avail_date', array(
            'header'    => Mage::helper('udropship')->__('Availability Date'),
            'type'      => 'date',
            'index'     => 'avail_date',
            'renderer'  => 'udropship/adminhtml_vendor_helper_gridRenderer_date',
            'editable'  => true, 'edit_only'  => true,
            'sortable'  => false,
            'filter'    => false,
        ));
        }
        if (Mage::helper('udropship')->isUdmultiPriceAvailable()) {
            $this->addColumn('_special_price', array(
                'header'    => Mage::helper('udropship')->__('Special Price'),
                'index'     => 'special_price',
                'renderer'  => 'udropship/adminhtml_vendor_helper_gridRenderer_specialPrice',
                'editable'  => true, 'edit_only'  => true,
                'sortable'  => false,
                'filter'    => false,
            ));
        }
        /*
        $this->addColumn('priority', array(
            'header'    => Mage::helper('udropship')->__('Priority'),
            'width'     => '70',
            'type'      => 'number',
            'index'     => 'priority',
            'editable'  => true
            'renderer'  => 'adminhtml/widget_grid_column_renderer_input'
        ));
        */
        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/productGrid', array('_current'=>true));
    }

    protected function _getSelectedProducts()
    {
        $json = $this->getRequest()->getPost('vendor_products');
        if (!is_null($json)) {
            $products = array_keys((array)Zend_Json::decode($json));
        } else {
            $products = $this->getVendor()->getAssociatedProductIds();
        }
        return $products;
    }
}
