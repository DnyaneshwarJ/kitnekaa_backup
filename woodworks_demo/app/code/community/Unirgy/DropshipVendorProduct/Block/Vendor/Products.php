<?php

class Unirgy_DropshipVendorProduct_Block_Vendor_Products extends Mage_Core_Block_Template
{
    protected $_collection;
    protected $_oldStoreId;
    protected $_unregUrlStore;

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        if (!Mage::registry('url_store')) {
            $this->_unregUrlStore = true;
            Mage::register('url_store', Mage::app()->getStore());
        }
        $this->_oldStoreId = Mage::app()->getStore()->getId();
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

        if ($toolbar = $this->getLayout()->getBlock('udprod.grid.toolbar')) {
            $toolbar->setCollection($this->getProductCollection());
        }

        foreach ($this->getProductCollection() as $p) {
            if (!Mage::helper('udropship')->isUdmultiAvailable()) {
                if (($vsAttrCode = Mage::getStoreConfig('udropship/vendor/vendor_sku_attribute')) && Mage::helper('udropship')->checkProductAttribute($vsAttrCode)) {
                    $p->setVendorSku($p->getData($vsAttrCode));
                }
            }
        }

        return $this;
    }

    protected function _getUrlModelClass()
    {
        return 'core/url';
    }
    public function getUrl($route = '', $params = array())
    {
        if (!isset($params['_store']) && $this->_oldStoreId) {
            $params['_store'] = $this->_oldStoreId;
        }
        return parent::getUrl($route, $params);
    }

    protected function _afterToHtml($html)
    {
        if ($this->_unregUrlStore) {
            $this->_unregUrlStore = false;
            Mage::unregister('url_store');
        }
        Mage::app()->setCurrentStore($this->_oldStoreId);
        return parent::_afterToHtml($html);
    }

    protected function _applyRequestFilters($collection)
    {
        $r = Mage::app()->getRequest();
        $param = $r->getParam('filter_sku');
        if (!is_null($param) && $param!=='') {
            $collection->addAttributeToFilter('sku', array('like'=>$param.'%'));
        }
        $param = $r->getParam('filter_vendor_sku');
        if (!is_null($param) && $param!=='') {
            $vsAttrCode = Mage::getStoreConfig('udropship/vendor/vendor_sku_attribute');
            if (Mage::helper('udropship')->isUdmultiAvailable()) {
                $collection->getSelect()->where('uvp.vendor_sku like ?', $param.'%');
            } elseif ($vsAttrCode && Mage::helper('udropship')->checkProductAttribute($vsAttrCode)) {
                $collection->addAttributeToFilter($vsAttrCode, array('like'=>$param.'%'));
            }
        }
        $param = $r->getParam('filter_name');
        if (!is_null($param) && $param!=='') {
            $collection->addAttributeToFilter('name', array('like'=>'%'.$param.'%'));
        }
        $param = $r->getParam('filter_system_status');
        if (!is_null($param) && $param!=='') {
            $collection->addAttributeToFilter('status', $param);
        }
        $param = $r->getParam('filter_stock_status');
        if (!is_null($param) && $param!=='') {
            $collection->getSelect()->where($this->_getStockField('status').'=?', $param);
        }
        $param = $r->getParam('filter_stock_qty_from');
        if (!is_null($param) && $param!=='') {
            //$collection->addAttributeToFilter('_stock_qty', array('gteq'=>$param));
            $collection->getSelect()->where($this->_getStockField('qty').'>=?', $param);
        }
        $param = $r->getParam('filter_stock_qty_to');
        if (!is_null($param) && $param!=='') {
            //$collection->addAttributeToFilter('_stock_qty', array('lteq'=>$param));
            $collection->getSelect()->where($this->_getStockField('qty').'<=?', $param);
        }
        $param = $r->getParam('filter_price_from');
        if (!is_null($param) && $param!=='') {
            $collection->addAttributeToFilter('price', array('gteq'=>$param));
        }
        $param = $r->getParam('filter_price_to');
        if (!is_null($param) && $param!=='') {
            $collection->addAttributeToFilter('price', array('lteq'=>$param));
        }
        return $this;
    }

    protected function _getStockField($type)
    {
        $v = Mage::getSingleton('udropship/session')->getVendor();
        if (!$v || !$v->getId()) {
            $isLocalVendor = 0;
        } else {
            $isLocalVendor = intval($v->getId()==Mage::getStoreConfig('udropship/vendor/local_vendor'));
        }
        if (Mage::helper('udropship')->isUdmultiActive()) {
            switch ($type) {
                case 'qty':
                    return new Zend_Db_Expr('IF(uvp.vendor_product_id is null, cisi.qty, uvp.stock_qty)');
                case 'status':
                    return new Zend_Db_Expr("IF(uvp.vendor_product_id is null or $isLocalVendor, cisi.is_in_stock, null)");
            }
        } else {
            switch ($type) {
                case 'qty':
                    return 'ciss.qty';
                case 'status':
                    return 'ciss.stock_status';
            }
        }
    }

    public function getProductCollection()
    {
        if (!$this->_collection) {
            $v = Mage::getSingleton('udropship/session')->getVendor();
            if (!$v || !$v->getId()) {
                return array();
            }
            $r = Mage::app()->getRequest();
            $res = Mage::getSingleton('core/resource');
            #$read = $res->getConnection('catalog_product');
            $stockTable = $res->getTableName('cataloginventory/stock_item');
            $stockStatusTable = $res->getTableName('cataloginventory/stock_status');
            $wId = (int)Mage::app()->getDefaultStoreView()->getWebsiteId();
            $collection = Mage::getResourceModel('udprod/product_collection')
                ->setFlag('has_group_entity', 1)
                ->addAttributeToFilter('type_id', array('in'=>array('simple','configurable','downloadable','virtual')))
                ->addAttributeToSelect(array('sku', 'name', 'status', 'price'))
            ;
            $collection->addAttributeToFilter('entity_id', array('in'=>$v->getAssociatedProductIds()));
            $collection->addAttributeToFilter('visibility', array('in'=>Mage::getSingleton('catalog/product_visibility')->getVisibleInSiteIds()));
            $conn = $collection->getConnection();
            $wIdsSql = $conn->quote(array_keys(Mage::app()->getWebsites()));
            //$collection->addAttributeToFilter('entity_id', array('in'=>array_keys($v->getAssociatedProducts())));
            $collection->getSelect()
                ->join(
                array('cisi' => $stockTable),
                $conn->quoteInto('cisi.product_id=e.entity_id AND cisi.stock_id=?', Mage_CatalogInventory_Model_Stock::DEFAULT_STOCK_ID),
                    array()
                )
                ->joinLeft(
                    array('ciss' => $stockStatusTable),
                    $conn->quoteInto('ciss.product_id=e.entity_id AND ciss.website_id in ('.$wIdsSql.') AND ciss.stock_id=?', Mage_CatalogInventory_Model_Stock::DEFAULT_STOCK_ID),
                array('_stock_status'=>$this->_getStockField('status'))
            );
            if (Mage::helper('udropship')->isUdmultiAvailable()) {
                $collection->getSelect()->joinLeft(
                    array('uvp' => $res->getTableName('udropship/vendor_product')),
                    $conn->quoteInto('uvp.product_id=e.entity_id AND uvp.vendor_id=?', $v->getId()),
                    array('_stock_qty'=>$this->_getStockField('qty'), 'vendor_sku'=>'uvp.vendor_sku', 'vendor_cost'=>'uvp.vendor_cost')
                );
                //$collection->getSelect()->columns(array('_stock_qty'=>'IFNULL(uvp.stock_qty,cisi.qty'));
            } else {
                if (($vsAttrCode = Mage::getStoreConfig('udropship/vendor/vendor_sku_attribute')) && Mage::helper('udropship')->checkProductAttribute($vsAttrCode)) {
                    $collection->addAttributeToSelect(array($vsAttrCode));
                }
                $collection->getSelect()->columns(array('_stock_qty'=>$this->_getStockField('qty')));
            }
$collection->addAttributeToFilter('udropship_vendor', $v->getId());

            $this->_applyRequestFilters($collection);

            $collection->getSelect()->group('e.entity_id');
            $collection->getSize();

            #Mage::getModel('cataloginventory/stock')->addItemsToProducts($collection);
            $this->_collection = $collection;
        }
        return $this->_collection;
    }
    public function getSetIdSelectHtml()
    {
        $options = Mage::getResourceModel('eav/entity_attribute_set_collection')
            ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getEntityType()->getId())
            ->load()
            ->toOptionArray();
        array_unshift($options, array('value'=>'','label'=>'* Please select'));
        return $this->getLayout()->createBlock('core/html_select')
            ->setName('set_id')
            ->setId('set_id')
            ->setTitle(Mage::helper('udropship')->__('Attribute Set'))
            ->setClass('validate-select absolute-advice')
            ->setOptions($options)->toHtml();
    }
}