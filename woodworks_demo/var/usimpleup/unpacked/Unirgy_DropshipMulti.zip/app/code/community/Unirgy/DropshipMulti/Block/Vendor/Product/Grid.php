<?php

class Unirgy_DropshipMulti_Block_Vendor_Product_Grid extends Mage_Core_Block_Template
{
    protected $_collection;

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if ($toolbar = $this->getLayout()->getBlock('product.grid.toolbar')) {
            $toolbar->setCollection($this->getProductCollection());
            $this->setChild('toolbar', $toolbar);
        }

        return $this;
    }

    public function getProductCollection()
    {
        $v = Mage::getSingleton('udropship/session')->getVendor();
        if (!$v || !$v->getId()) {
            return array();
        }
        $r = Mage::app()->getRequest();
        #$res = Mage::getSingleton('core/resource');
        #$read = $res->getConnection('catalog_product');
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('name')
            ->joinTable('udropship/vendor_product', 'product_id=entity_id', array('vendor_product_id', 'vendor_sku', 'vendor_cost', 'stock_qty'), '{{table}}.vendor_id='.$v->getId());
            
        $param = $r->getParam('filter_sku');
        if (!is_null($param) && $param!=='') {
            $collection->addAttributeToFilter('sku', array('like'=>$param.'%'));
        }
        $param = $r->getParam('filter_name');
        if (!is_null($param) && $param!=='') {
            $collection->addAttributeToFilter('name', array('like'=>$param.'%'));
        }
        $param = $r->getParam('filter_vendor_sku');
        if (!is_null($param) && $param!=='') {
            $collection->getSelect()->where('vendor_sku like ?', $param.'%');
        }
        $param = $r->getParam('filter_vendor_cost_from');
        if (!is_null($param) && $param!=='') {
            $collection->getSelect()->where('vendor_cost>=?', $param);
        }
        $param = $r->getParam('filter_vendor_cost_to');
        if (!is_null($param) && $param!=='') {
            $collection->getSelect()->where('vendor_cost<=?', $param);
        }
        $param = $r->getParam('filter_stock_qty_from');
        if (!is_null($param) && $param!=='') {
            $collection->getSelect()->where('stock_qty>=?', $param);
        }
        $param = $r->getParam('filter_stock_qty_to');
        if (!is_null($param) && $param!=='') {
            $collection->getSelect()->where('stock_qty<=?', $param);
        }

        return $collection;
    }
}