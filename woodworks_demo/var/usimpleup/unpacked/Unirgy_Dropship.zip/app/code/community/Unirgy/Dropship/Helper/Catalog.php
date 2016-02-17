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

class Unirgy_Dropship_Helper_Catalog extends Mage_Core_Helper_Abstract
{
    public function isQty($product)
    {
        return Mage::helper('cataloginventory')->isQty($product->getTypeId());
    }
    protected $_topCats;
    public function getTopCategories()
    {
        if (null === $this->_topCats) {
            $this->_topCats = $this->getCategoryChildren(
                $this->getStoreRootCategory($this->getStore())
            );
        }
        return $this->_topCats;
    }
    public function getStore()
    {
        return Mage::app()->getDefaultStoreView();
    }
    protected $_rootCnt;
    protected $_storeRootCategory = array();
    public function getStoreRootCategory($store=null)
    {
        if ($this->_rootCnt===null) {
            $res = Mage::getSingleton('core/resource');
            $read = $res->getConnection('catalog_read');
            $select = $read->select()
                ->from($res->getTableName('core/store_group'), 'COUNT(distinct root_category_id)')
                ->where('root_category_id!=0');
            $this->_rootCnt = $read->fetchOne($select);
        }
        if ($store === null && $this->_rootCnt>1) {
            $rootId = Mage_Catalog_Model_Category::TREE_ROOT_ID;
        } else {
            $store = Mage::app()->getStore($store);
            $rootId = $store->getRootCategoryId();
            if (!$rootId) $rootId = $this->getStore()->getRootCategoryId();
            if (!$rootId) $rootId = $this->getStore()->getGroup()->getRootCategoryId();
            if (!$rootId) $rootId = Mage_Catalog_Model_Category::TREE_ROOT_ID;

        }
        if (!isset($this->_storeRootCategory[$rootId])) {

            $this->_storeRootCategory[$rootId] = Mage::getModel('catalog/category')->load($rootId);
        }
        return $this->_storeRootCategory[$rootId];
    }
    public function getPathInStore($cat)
    {
        $result = array();
        $path = array_reverse($cat->getPathIds());
        foreach ($path as $itemId) {
            if ($itemId == $this->getStore()->getRootCategoryId()) {
                break;
            }
            $result[] = $itemId;
        }
        return implode(',', $result);
    }
    public function getCategoryChildren($cId, $active=true, $recursive=false)
    {
        return $this->_getCategoryChildren($cId, $active, $recursive);
    }
    protected function _getCategoryChildren($cId, $active=true, $recursive=false, $orderBy='level,position')
    {
        if ($cId instanceof Mage_Catalog_Model_Category) {
            $cat = $cId;
        } else {
            $cat = Mage::getModel('catalog/category')->load($cId);
        }
        $collection = $cat->getCollection()
            ->addAttributeToSelect('url_key')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('all_children')
            ->addAttributeToSelect('is_anchor');
        $orderBy = explode(',', $orderBy);
        foreach ($orderBy as $ob) {
            $ob = explode(':', $ob);
            $ob[1] = !empty($ob[1]) ? $ob[1] : 'asc';
            $collection->setOrder($ob[0], $ob[1]);
        }
        if (Mage::helper('catalog/category_flat')->isEnabled()) {
            $collection->addUrlRewriteToResult();
        } else {
            $collection->joinUrlRewrite();
        }
        if ($active) {
            $collection->addAttributeToFilter('is_active', 1);
        }
        $collection->getSelect()->where('path LIKE ?', "{$cat->getPath()}/%");
        if (!$recursive) {
            $collection->getSelect()->where('level <= ?', $cat->getLevel() + 1);
        }
        return $collection;
    }
    public function getCategoriesCollection($cIds, $active=true, $orderBy='level,position')
    {
        $collection = Mage::getModel('catalog/category')->getCollection()
            ->addAttributeToSelect('url_key')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('all_children')
            ->addAttributeToSelect('is_anchor');
        $orderBy = explode(',', $orderBy);
        foreach ($orderBy as $ob) {
            $ob = explode(':', $ob);
            $ob[1] = !empty($ob[1]) ? $ob[1] : 'asc';
            $collection->setOrder($ob[0], $ob[1]);
        }
        if (Mage::helper('catalog/category_flat')->isEnabled()) {
            $collection->addUrlRewriteToResult();
        } else {
            $collection->joinUrlRewrite();
        }
        if ($active) {
            $collection->addAttributeToFilter('is_active', 1);
        }
        $collection->addIdFilter($cIds);
        return $collection;
    }
    public function processCategoriesData(&$fCatIds, $returnArray=true)
    {
        if (!is_array($fCatIds)) {
            if (strpos($fCatIds, 'a:')===0) {
                $fCatIds = @unserialize($fCatIds);
            } elseif (strpos($fCatIds, '{')===0) {
                $fCatIds = Zend_Json::decode($fCatIds);
            }
        }
        if (is_array($fCatIds) && !$returnArray) {
            $fCatIds = implode(',', $fCatIds);
        } elseif (!is_array($fCatIds) && $returnArray) {
            $fCatIds = explode(',', $fCatIds);
        }
        $fCatIds = $fCatIds === null ? '' : $fCatIds;
        return $this;
    }
    protected $_store;
    protected $_oldStore;
    protected $_oldArea;
    protected $_oldDesign;
    protected $_oldTheme;

    public function setDesignStore($store=null, $area=null, $theme=null)
    {
        if (!is_null($store)) {
            if ($this->_store) {
                return $this;
            }
            $this->_oldStore = Mage::app()->getStore();
            $this->_oldArea = Mage::getDesign()->getArea();
            $this->_store = Mage::app()->getStore($store);

            $_theme = array();
            $store = $this->_store;
            $area = $area ? $area : 'frontend';
            if ($area == 'adminhtml') {
                $package = (string)Mage::getConfig()->getNode('stores/admin/design/package/name');
                $design = array('package'=>$package, 'store'=>$store->getId());
                $_theme['default'] = (string)Mage::getConfig()->getNode('stores/admin/design/theme/default');
                foreach (array('layout', 'template', 'skin', 'locale') as $type) {
                    $_theme[$type] = (string)Mage::getConfig()->getNode("stores/admin/design/theme/{$type}");
                }
            } else {
                $package = Mage::getStoreConfig('design/package/name', $store);
                $design = array('package'=>$package, 'store'=>$store->getId());
                $_theme['default'] = (string)$store->getConfig("design/theme/default");
                foreach (array('layout', 'template', 'skin', 'locale') as $type) {
                    $_theme[$type] = (string)$store->getConfig("design/theme/{$type}");
                }
            }
            if ($theme!==null) {
                if (!is_array($theme) && is_scalar($theme)) {
                    $theme = explode('/',$theme);
                }
                if (!is_array($theme)) {
                    $theme = array();
                }
                if (isset($theme[0]) && !empty($theme[0])) {
                    $design['package'] = (string)$theme[0];
                }
                if (isset($theme[1]) && !empty($theme[1])) {
                    $_theme['default'] = (string)$theme[1];
                    foreach (array('layout', 'template', 'skin', 'locale') as $type) {
                        $_theme[$type] = (string)$theme[1];
                    }
                }
            }
            $inline = false;
        } else {
            if (!$this->_store) {
                return $this;
            }
            $this->_store = null;
            $store = $this->_oldStore;
            $area = $this->_oldArea;
            $design = $this->_oldDesign;
            $_theme = $this->_oldTheme;
            $inline = true;
        }

        Mage::app()->setCurrentStore($store);
        $oldDesign = Mage::getDesign()->setArea($area)->setAllGetOld($design);
        foreach (array('default', 'layout', 'template', 'skin', 'locale') as $type) {
            $oldTheme[$type] = Mage::getDesign()->getTheme($type);
            Mage::getDesign()->setTheme($type, @$_theme[$type]);
        }
        Mage::app()->getLayout()->setArea($area);
        Mage::app()->getTranslator()->init($area, true);
        Mage::getSingleton('core/translate')->setTranslateInline($inline);

        if ($this->_store) {
            $this->_oldDesign = $oldDesign;
            $this->_oldTheme = $oldTheme;
        } else {
            $this->_oldStore = null;
            $this->_oldArea = null;
            $this->_oldDesign = null;
            $this->_oldTheme = null;
        }

        return $this;
    }
    public function getPidBySku($sku, $excludePids=null)
    {
        return $this->_getPidBySku($sku, $excludePids);
    }
    public function getPidBySkuForUpdate($sku, $excludePids=null)
    {
        return $this->_getPidBySku($sku, $excludePids, true);
    }
    protected function _getPidBySku($sku, $excludePids=null, $forUpdate=false)
    {
        $res = Mage::getSingleton('core/resource');
        $read = $res->getConnection('catalog_read');
        $table = $res->getTableName('catalog/product');
        $select = $read->select()
            ->from($table, 'entity_id')
            ->where('sku = :sku');
        $bind = array(':sku' => (string)trim($sku));
        if (!empty($excludePids)) {
            if (!is_array($excludePids)) {
                $excludePids = array($excludePids);
            }
            $select->where('entity_id not in (?)', $excludePids);
        }
        if ($forUpdate) {
            $select->forUpdate(true);
        }
        return $read->fetchOne($select, $bind);
    }
    public function getPidByVendorSku($vSku, $vId, $excludePids=null)
    {
        $pId = null;
        if (Mage::helper('udropship')->isUdmultiActive()) {
            $res = Mage::getSingleton('core/resource');
            $read = $res->getConnection('udropship_read');
            $table = $res->getTableName('udropship_vendor_product');
            $select = $read->select()
                ->from($table, 'product_id')
                ->where('vendor_sku = :vendor_sku and vendor_id = :vendor_id');
            $bind = array(':vendor_sku' => (string)trim($vSku), ':vendor_id' => $vId);
            if (!empty($excludePids)) {
                if (!is_array($excludePids)) {
                    $excludePids = array($excludePids);
                }
                $select->where('product_id not in (?)', $excludePids);
            }
            $pId = $read->fetchOne($select, $bind);
        } else {
            $vSkuAttr = Mage::getStoreConfig('udropship/vendor/vendor_sku_attribute');
            if ($vSkuAttr && $vSkuAttr!='sku') {
                $attrFilters = array(array(
                    'attribute' => $vSkuAttr,
                    'in' => array($vSku)
                ));
                if (!empty($excludePids)) {
                    if (!is_array($excludePids)) {
                        $excludePids = array($excludePids);
                    }
                    $attrFilters[] = array(
                        'attribute' => 'entity_id',
                        'nin' => $excludePids
                    );
                }
                $prodCol = Mage::getModel('catalog/product')->getCollection()->setPage(1,1)
                    ->addAttributeToSelect($vSkuAttr)
                    ->addAttributeToFilter('udropship_vendor', $vId);
                foreach ($attrFilters as $attrFilter) {
                    $prodCol->addAttributeToFilter($attrFilter['attribute'], $attrFilter);
                }
                $pId = $prodCol->getFirstItem()->getId();
            }
        }
        return $pId;
    }
    public function getVendorSkuByPid($pId, $vId)
    {
        $vSku = null;
        if (Mage::helper('udropship')->isUdmultiActive()) {
            $res = Mage::getSingleton('core/resource');
            $read = $res->getConnection('udropship_read');
            $table = $res->getTableName('udropship_vendor_product');
            $select = $read->select()
                ->from($table, 'vendor_sku')
                ->where('product_id = :product_id and vendor_id = :vendor_id');
            $bind = array(':product_id' => (string)trim($pId), ':vendor_id' => $vId);
            $vSku = $read->fetchOne($select, $bind);
        } else {
            $vSkuAttr = Mage::getStoreConfig('udropship/vendor/vendor_sku_attribute');
            if ($vSkuAttr && $vSkuAttr!='sku') {
                $attrFilters = array(array(
                    'attribute' => 'entity_id',
                    'in' => array($pId)
                ));
                $prodCol = Mage::getModel('catalog/product')->getCollection()->setPage(1,1)
                    ->addAttributeToSelect($vSkuAttr)
                    ->addAttributeToFilter($attrFilters);
                if ($prodCol->getFirstItem()->getId()) {
                    $vSku = $prodCol->getFirstItem()->getData($vSkuAttr);
                }
            }
        }
        return $vSku;
    }

    public function reindexPids($pIds)
    {
        $indexer = Mage::getSingleton('index/indexer');
        $pAction = Mage::getModel('catalog/product_action');
        $idxEvent = Mage::getModel('index/event')
            ->setEntity(Mage_Catalog_Model_Product::ENTITY)
            ->setType(Mage_Index_Model_Event::TYPE_MASS_ACTION)
            ->setDataObject($pAction);
        /* hook to cheat index process to be executed */
        $pAction->setWebsiteIds(array(0));
        $pAction->setProductIds($pIds);
        foreach (array(
            'cataloginventory_stock','catalog_product_attribute','catalog_product_price',
            'tag_summary','catalog_category_product','udropship_vendor_product_assoc'
        ) as $idxKey
        ) {
            $indexer->getProcessByCode($idxKey)->register($idxEvent)->processEvent($idxEvent);
        }
        Mage::getSingleton('catalogsearch/fulltext')->rebuildIndex(null, $pIds);
        foreach ($pIds as $pId) {
            Mage::getSingleton('catalog/product_flat_indexer')->updateProduct($pId);
            Mage::getSingleton('catalog/url')->refreshProductRewrite($pId);
        }
    }

    public function getWebsiteValues($hash=false, $selector=true)
    {
        $values = array();
        if ($selector) {
            if ($hash) {
                $values[''] = Mage::helper('udropship')->__('* Select category');
            } else {
                $values[] = array('label'=>Mage::helper('udropship')->__('* Select category'), 'value'=>'');
            }
        }
        foreach (Mage::app()->getWebsites() as $website) {
            if ($hash) {
                $values[$website->getId()] = $website->getName();
            } else {
                $values[] = array('label'=>$website->getName(), 'value'=>$website->getId());
            }
        }
        return $values;
    }
    public function getCategoryValues($hash=false, $selector=true)
    {
        $values = array();
        if ($selector) {
            if ($hash) {
                $values[''] = Mage::helper('udropship')->__('* Select category');
            } else {
                $values[] = array('label'=>Mage::helper('udropship')->__('* Select category'), 'value'=>'');
            }
        }
        $cat = Mage::helper('udropship/catalog')->getStoreRootCategory();
        $this->_attachCategoryValues($cat, $values, 0, $hash);
        return $values;
    }
    protected function _attachCategoryValues($cat, &$values, $level, $hash=false)
    {
        $children = $cat->getChildrenCategories();
        if (count($children)>0) {
            if ($hash) {
                $values[$cat->getId()] = $cat->getName();
            } else {
                $values[] = array('label'=>$cat->getName(), 'value'=>$cat->getId(), 'level'=>$level, 'disabled'=>true);
            }
            $level+=1;
            foreach ($children as $child) {
                $this->_attachCategoryValues($child, $values, $level, $hash);
            }
        } else {
            if ($hash) {
                $values[$cat->getId()] = $cat->getName();
            } else {
                $values[] = array('label'=>$cat->getName(), 'value'=>$cat->getId(), 'level'=>$level);
            }
        }
        return $this;
    }

    public function createCfgAttr($cfgProd, $cfgAttrId, $pos)
    {
        $cfgPid = $cfgProd;
        if ($cfgProd instanceof Mage_Catalog_Model_Product) {
            $cfgPid = $cfgProd->getId();
        }
        $res = Mage::getSingleton('core/resource');
        $write = $res->getConnection('catalog_write');
        $superAttrTable = $res->getTableName('catalog/product_super_attribute');
        $superLabelTable = $res->getTableName('catalog/product_super_attribute_label');

        $exists = $write->fetchRow("select sa.*, sal.value_id, sal.value label from {$superAttrTable} sa
            inner join {$superLabelTable} sal on sal.product_super_attribute_id=sa.product_super_attribute_id
            where sa.product_id={$cfgPid} and sa.attribute_id={$cfgAttrId} and sal.store_id=0");
        if (!$exists) {
            $write->insert($superAttrTable, array(
                'product_id' => $cfgPid,
                'attribute_id' => $cfgAttrId,
                'position' => $pos,
            ));
            $saId = $write->lastInsertId($superAttrTable);
            $write->insert($superLabelTable, array(
                'product_super_attribute_id' => $saId,
                'store_id' => 0,
                'use_default' => 1,
                'value' => '',
            ));
        }

        return $this;
    }

    public function getCfgSimpleSkus($cfgPid)
    {
        $res = Mage::getSingleton('core/resource');
        $write = $res->getConnection('catalog_write');
        $t = $res->getTableName('catalog/product_super_link');
        $t2 = $res->getTableName('catalog/product');
        return $write->fetchCol("select {$t2}.sku from {$t} inner join {$t2} on {$t2}.entity_id={$t}.product_id
            where parent_id='{$cfgPid}'");
    }

    public function getCfgSimplePids($cfgPid)
    {
        $res = Mage::getSingleton('core/resource');
        $write = $res->getConnection('catalog_write');
        $t = $res->getTableName('catalog/product_super_link');
        $t2 = $res->getTableName('catalog/product');
        return $write->fetchCol("select {$t2}.entity_id from {$t} inner join {$t2} on {$t2}.entity_id={$t}.product_id
            where parent_id='{$cfgPid}'");
    }

    public function unlinkCfgSimple($cfgPid, $simpleSku, $byPid=false)
    {
        $res = Mage::getSingleton('core/resource');
        $write = $res->getConnection('catalog_write');
        $t = $res->getTableName('catalog/product_super_link');
        $t2 = $res->getTableName('catalog/product_relation');

        $p2 = $byPid ? $simpleSku : Mage::helper('udropship/catalog')->getPidBySku($simpleSku);

        $linkId = $write->fetchCol("select link_id from {$t}
            where parent_id='{$cfgPid}' and product_id='{$p2}'");
        if ($linkId) {
            $write->delete($t,$write->quoteInto("link_id in (?)", $linkId));
            $write->delete($t2, "parent_id={$cfgPid} and child_id={$p2}");
        }
        return $this;
    }

    public function linkCfgSimple($cfgPid, $simpleSku, $byPid=false)
    {
        $res = Mage::getSingleton('core/resource');
        $write = $res->getConnection('catalog_write');
        $t = $res->getTableName('catalog/product_super_link');

        $p2 = $byPid ? $simpleSku : Mage::helper('udropship/catalog')->getPidBySku($simpleSku);

        $linkId = $write->fetchOne("select link_id from {$t} where parent_id='{$cfgPid}' and product_id='{$p2}'");
        if (!$linkId && $p2) {
            $write->insert($t, array('parent_id'=>$cfgPid, 'product_id'=>$p2));
            $relTable = $res->getTableName('catalog/product_relation');
            if (!$write->fetchOne("select parent_id from {$relTable} where parent_id={$cfgPid} and child_id={$p2}")) {
                $write->insert($relTable, array('parent_id'=>$cfgPid, 'child_id'=>$p2));
            }
        }
        return $this;
    }
    public function getSortedCategoryChildren($cId, $orderBy, $active=true, $recursive=false)
    {
        return $this->_getCategoryChildren($cId, $active, $recursive, $orderBy);
    }

    public function addProductAttributeToSelect($select, $attrCode, $entity_id)
    {
        $alias = $attrCode;
        if (is_array($attrCode)) {
            reset($attrCode);
            $alias = key($attrCode);
            $attrCode = current($attrCode);
        }
        $attribute = Mage::getSingleton('eav/config')->getAttribute(Mage_Catalog_Model_Product::ENTITY, $attrCode);
        if (!$attribute || !$attribute->getAttributeId()) {
            $select->columns(array($alias=>new Zend_Db_Expr("''")));
            return $this;
        }
        $attributeId    = $attribute->getAttributeId();
        $attributeTable = $attribute->getBackend()->getTable();
        $adapter        = $select->getAdapter();
        $store = Mage::app()->getStore()->getId();

        if ($attribute->isScopeGlobal()) {
            $_alias = 'ta_' . $attrCode;
            $select->joinLeft(
                array($_alias => $attributeTable),
                "{$_alias}.entity_id = {$entity_id} AND {$_alias}.attribute_id = {$attributeId}"
                . " AND {$_alias}.store_id = 0",
                array()
            );
            $expression = new Zend_Db_Expr("{$_alias}.value");
        } else {
            $dAlias = 'tad_' . $attrCode;
            $sAlias = 'tas_' . $attrCode;

            $select->joinLeft(
                array($dAlias => $attributeTable),
                "{$dAlias}.entity_id = {$entity_id} AND {$dAlias}.attribute_id = {$attributeId}"
                . " AND {$dAlias}.store_id = 0",
                array()
            );
            $select->joinLeft(
                array($sAlias => $attributeTable),
                "{$sAlias}.entity_id = {$entity_id} AND {$sAlias}.attribute_id = {$attributeId}"
                . " AND {$sAlias}.store_id = {$store}",
                array()
            );
            $expression = $this->getCheckSql($this->getIfNullSql("{$sAlias}.value_id", -1) . ' > 0',
                "{$sAlias}.value", "{$dAlias}.value");
        }

        $select->columns(array($alias=>$expression));

        return $this;
    }

    public function getCaseSql($valueName, $casesResults, $defaultValue = null)
    {
        $expression = 'CASE ' . $valueName;
        foreach ($casesResults as $case => $result) {
            $expression .= ' WHEN ' . $case . ' THEN ' . $result;
        }
        if ($defaultValue !== null) {
            $expression .= ' ELSE ' . $defaultValue;
        }
        $expression .= ' END';

        return new Zend_Db_Expr($expression);
    }

    public function getCheckSql($expression, $true, $false)
    {
        if ($expression instanceof Zend_Db_Expr || $expression instanceof Zend_Db_Select) {
            $expression = sprintf("IF((%s), %s, %s)", $expression, $true, $false);
        } else {
            $expression = sprintf("IF(%s, %s, %s)", $expression, $true, $false);
        }

        return new Zend_Db_Expr($expression);
    }

    public function getIfNullSql($expression, $value = 0)
    {
        if ($expression instanceof Zend_Db_Expr || $expression instanceof Zend_Db_Select) {
            $expression = sprintf("IFNULL((%s), %s)", $expression, $value);
        } else {
            $expression = sprintf("IFNULL(%s, %s)", $expression, $value);
        }

        return new Zend_Db_Expr($expression);
    }
}