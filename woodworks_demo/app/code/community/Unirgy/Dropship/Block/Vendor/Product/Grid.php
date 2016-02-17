<?php

class Unirgy_Dropship_Block_Vendor_Product_Grid extends Mage_Core_Block_Template
{
    protected $_collection;

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $oldStoreId = Mage::app()->getStore()->getId();
        $this->_oldStoreId = Mage::app()->getStore()->getId();
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
        if ($toolbar = $this->getLayout()->getBlock('product.grid.toolbar')) {
            $toolbar->setCollection($this->getProductCollection());
            $this->setChild('toolbar', $toolbar);
        }
        $this->getProductCollection()->load();

        if (Mage::helper('udropship')->isModuleActive('Unirgy_DropshipSellYours')) {
            $findEditOfferIds = array();
            foreach ($this->getProductCollection() as $p) {
                if (!$p->isVisibleInSiteVisibility()) {
                    $findEditOfferIds[] = $p->getEntityId();
                    $p->setHasEditOfferId(1);
                }
            }
            if (!empty($findEditOfferIds)) {
                $rHlp = Mage::getResourceSingleton('udropship/helper');
                $conn = $rHlp->getReadConnection();
                $findEditOffersSel = $conn->select()
                    ->from($rHlp->getTable('catalog/product_super_link'))
                    ->where('product_id in (?)', $findEditOfferIds);
                $findEditOffers = $conn->fetchAll(
                    $findEditOffersSel
                );
                if (is_array($findEditOffers)) {
                    foreach ($findEditOffers as $__feo) {
                        foreach ($this->getProductCollection() as $p) {
                            if ($__feo['product_id']==$p->getEntityId()) {
                                $p->setEditOfferId($__feo['parent_id']);
                                break;
                            }
                        }
                    }
                }
            }
        }


        foreach ($this->getProductCollection() as $p) {
            if (!Mage::helper('udropship')->isUdmultiAvailable()) {
                if (($vsAttrCode = Mage::getStoreConfig('udropship/vendor/vendor_sku_attribute')) && Mage::helper('udropship')->checkProductAttribute($vsAttrCode)) {
                    $p->setVendorSku($p->getData($vsAttrCode));
                }
            }
        }

        Mage::app()->getStore()->setId($oldStoreId);
        Mage::app()->setCurrentStore($this->_oldStoreId);

        return $this;
    }
    
    protected function _applyRequestFilters($collection)
    {
        $r = Mage::app()->getRequest();
        $param = $r->getParam('filter_sku');
        if (!is_null($param) && $param!=='') {
            $collection->addAttributeToFilter('sku', array('like'=>'%'.$param.'%'));
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
            $collection->addAttributeToFilter('name', array('like'=>$param.'%'));
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
        if (Mage::helper('udropship')->isUdmultiAvailable()) {
            switch ($type) {
                case 'is_qty':
                    return new Zend_Db_Expr('1');
                case 'qty':
                    return new Zend_Db_Expr('IF(uvp.vendor_product_id is null, cisi.qty, uvp.stock_qty)');
                case 'status':
                    return new Zend_Db_Expr("IF(uvp.vendor_product_id is null, cisi.is_in_stock, null)");
            }
        } else {
            $isManageStock = Mage::getStoreConfigFlag(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MANAGE_STOCK);
            switch ($type) {
                case 'is_qty':
                    return sprintf('IF (cisi.use_config_manage_stock && 0=%d || !cisi.use_config_manage_stock && 0=cisi.manage_stock, null, 1)', $isManageStock);
                case 'qty':
                    return 'cisi.qty';
                case 'status':
                    return sprintf('IF (cisi.use_config_manage_stock && 0=%d || !cisi.use_config_manage_stock && 0=cisi.manage_stock, null, cisi.is_in_stock)', $isManageStock);
            }
        }
    }

    protected $_oldStoreId;
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
            $collection = Mage::getModel('catalog/product')->getCollection()
                //->addAttributeToFilter('udropship_vendor', $v->getId())
                ->addAttributeToFilter('type_id', array('in'=>array('simple','downloadable','virtual')))
                ->addAttributeToSelect(array('sku', 'name', 'visibility'/*, 'cost'*/))
            ;
            $conn = $collection->getConnection();
            $collection->addAttributeToFilter('entity_id', array('in'=>$v->getAssociatedProductIds()));
            $collection->getSelect()->join(
                array('cisi' => $stockTable), 
                $conn->quoteInto('cisi.product_id=e.entity_id AND cisi.stock_id=?', Mage_CatalogInventory_Model_Stock::DEFAULT_STOCK_ID), 
                array('_stock_status'=>$this->_getStockField('status'), '_is_stock_qty'=>$this->_getStockField('is_qty'))
            );
            if (Mage::helper('udropship')->isUdmultiAvailable()) {
                $collection->getSelect()->join(
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

            $this->_applyRequestFilters($collection);

            $this->_collection = $collection;
        }
        return $this->_collection;
    }
}