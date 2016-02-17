<?php

class Unirgy_DropshipMulti_Block_Adminhtml_Vendor_Products
    extends Unirgy_Dropship_Block_Adminhtml_Vendor_Edit_Tab_Products
{
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
            ->joinTable('udropship/vendor_product', 'product_id=entity_id', array('vendor_sku', 'vendor_cost', 'stock_qty'), '{{table}}.vendor_id='.$this->getVendorId(), 'left');
        ;
        $this->setCollection($collection);

        return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
    }

    protected function _addColumnFilterToCollection($column)
    {
        $id = $column->getId();
        $value = $column->getFilter()->getValue();
        $select = $this->getCollection()->getSelect();
        switch ($id) {
        case 'vendor_sku':
            if (!is_null($value) && $value!=='') {
                $select->where('vendor_sku like ?', $column->getFilter()->getValue().'%');
            }
            break;

        case 'vendor_cost': case 'stock_qty':
            if (!is_null($value['from']) && $value['from']!=='') {
                $select->where($id.'>=?', $value['from']);
            }
            if (!is_null($value['to']) && $value['to']!=='') {
                $select->where($id.'<=?', $value['to']);
            }
            break;

        default:
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
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
            'header'    => Mage::helper('udropship')->__('Magento SKU'),
            'width'     => '80',
            'index'     => 'sku'
        ));
        $this->addColumn('_vendor_sku', array(
            'header'    => Mage::helper('udropship')->__('Vendor SKU'),
            'index'     => 'vendor_sku',
            'editable'  => true,
            'sortable'  => false,
            'filter'    => false,
            'width'     => '100',
        ));
        $this->addColumn('price', array(
            'header'    => Mage::helper('udropship')->__('Magento Price'),
            'type'      => 'currency',
            'currency_code' => (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
            'index'     => 'price'
        ));
        $this->addColumn('_vendor_cost', array(
            'header'    => Mage::helper('udropship')->__('Vendor Cost'),
            'type'      => 'number',
            'index'     => 'vendor_cost',
            'editable'  => true,
            'sortable'  => false,
            'filter'    => false,
        ));
        $this->addColumn('_stock_qty', array(
            'header'    => Mage::helper('udropship')->__('Vendor Stock Qty'),
            'type'      => 'number',
            'index'     => 'stock_qty',
            'editable'  => true,
            'sortable'  => false,
            'filter'    => false,
        ));
        return Mage_Adminhtml_Block_Widget_Grid::_prepareColumns();
    }
}
