<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_CatalogInventory
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */


/**
 * CatalogInventory Grouped Products Stock Status Indexer Resource Model
 *
 * @category    Mage
 * @package     Mage_CatalogInventory
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Unirgy_DropshipMulti_Model_StockIndexer_EE11300_Grouped
    extends Unirgy_DropshipMulti_Model_StockIndexer_EE11300_Default
{
    /**
     * Reindex stock data for defined configurable product ids
     *
     * @param int|array $entityIds
     * @return Mage_CatalogInventory_Model_Resource_Indexer_Stock_Grouped
     */
    public function reindexEntity($entityIds)
    {
        $this->_updateIndex($entityIds);
        return $this;
    }

    /**
     * Get the select object for get stock status by product ids
     *
     * @param int|array $entityIds
     * @param bool $usePrimaryTable use primary or temporary index table
     * @return Varien_Db_Select
     */
    protected function _getStockStatusSelect($entityIds = null, $usePrimaryTable = false)
    {
        $adapter  = $this->_getWriteAdapter();
        $idxTable = $usePrimaryTable ? $this->getMainTable() : $this->getIdxTable();
        $select   = $adapter->select()
            ->from(array('e' => $this->getTable('catalog/product')), array('entity_id'));
        $this->_addWebsiteJoinToSelect($select, true);
        $this->_addProductWebsiteJoinToSelect($select, 'cw.website_id', 'e.entity_id');
        $select->columns('cw.website_id')
            ->join(
                array('cis' => $this->getTable('cataloginventory/stock')),
                '',
                array('stock_id'))
            ->joinLeft(
                array('cisi' => $this->getTable('cataloginventory/stock_item')),
                'cisi.stock_id = cis.stock_id AND cisi.product_id = e.entity_id',
                array())
            ->joinLeft(
                array('l' => $this->getTable('catalog/product_link')),
                'e.entity_id = l.product_id AND l.link_type_id=' . Mage_Catalog_Model_Product_Link::LINK_TYPE_GROUPED,
                array())
            ->joinLeft(
                array('le' => $this->getTable('catalog/product')),
                'le.entity_id = l.linked_product_id',
                array())
            ->joinLeft(
                array('i' => $idxTable),
                'i.product_id = l.linked_product_id AND cw.website_id = i.website_id AND cis.stock_id = i.stock_id',
                array())
            ->columns(array('qty' => new Zend_Db_Expr('0')))
            ->where('cw.website_id != 0')
            ->where('e.type_id = ?', $this->getTypeId())
            ->group(array('e.entity_id', 'cw.website_id', 'cis.stock_id'));

        // add limitation of status
        $psExpr = $this->_addAttributeToSelect($select, 'status', 'e.entity_id', 'cs.store_id');
        $psCond = $adapter->quoteInto($psExpr . '=?', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);

        if ($this->_isManageStock()) {
            $statusExpr = $adapter->getCheckSql('cisi.use_config_manage_stock = 0 AND cisi.manage_stock = 0',
                1, 'cisi.is_in_stock');
        } else {
            $statusExpr = $adapter->getCheckSql('cisi.use_config_manage_stock = 0 AND cisi.manage_stock = 1',
                'cisi.is_in_stock', 1);
        }

        $optExpr = $adapter->getCheckSql("{$psCond} AND le.required_options = 0", 'i.stock_status', 0);
        $stockStatusExpr = $adapter->getLeastSql(array("MAX({$optExpr})", "MIN({$statusExpr})"));

        $select->columns(array(
            'status' => $stockStatusExpr
        ));

        if (!is_null($entityIds)) {
            $select->where('e.entity_id IN(?)', $entityIds);
        }

        return $select;
    }
    protected function _syncUdmWithStockItem($entityIds)
    {
        if (!Mage::helper('udmulti')->isActive()) {
            return $this;
        }
        $adapter = $this->_getWriteAdapter();
        $select  = $adapter->select()
            ->from(array('cisi' => $this->getTable('cataloginventory/stock_item')), array())
            ->join(array('e' => $this->getTable('catalog/product')),
                'cisi.stock_id = '.(int)Mage_CatalogInventory_Model_Stock::DEFAULT_STOCK_ID
                .' AND cisi.product_id = e.entity_id',
                array());
        $select->where('e.type_id = ?', $this->getTypeId());

        $select->joinLeft(
            array('l' => $this->getTable('catalog/product_link')),
            'e.entity_id = l.product_id AND l.link_type_id=' . Mage_Catalog_Model_Product_Link::LINK_TYPE_GROUPED,
            array())
            ->joinLeft(
                array('le' => $this->getTable('catalog/product')),
                'le.entity_id = l.linked_product_id',
                array())
            ->joinLeft(
                array('ssi' => $this->getTable('cataloginventory/stock_item')),
                'ssi.product_id = l.linked_product_id AND cisi.stock_id = ssi.stock_id',
                array());

        $uvpColumns = $adapter->describeTable($this->getTable('udropship/vendor_product'));
        $uvpCond = 'uvp.product_id=ssi.product_id';
        if (array_key_exists('status', $uvpColumns)) {
            $uvpCond .= ' AND uvp.status>0';
        }
        $select->joinLeft(
            array('uvp'=>$this->getTable('udropship/vendor_product')),
            $uvpCond,
            array())
            ->joinLeft(
                array('uv'=>$this->getTable('udropship/vendor')),
                'uv.vendor_id=uvp.vendor_id AND uv.status=\'A\'',
                array());
        $select->group(array('cisi.item_id'));
        $_qtyExpr = $adapter->getCheckSql(
            'uvp.stock_qty IS NULL',
            '10000', $adapter->getCheckSql('uvp.stock_qty>0', 'uvp.stock_qty', '0')
        );
        $qtyExpr = sprintf('MAX(%s)',
            $adapter->getCheckSql('uvp.vendor_product_id IS NULL OR uv.vendor_id IS NULL', '0', $_qtyExpr)
        );

        $cfgMinQty = (int)Mage::getStoreConfig(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MIN_QTY);

        $stockQtyExpr = $adapter->getCheckSql(
            'ssi.use_config_min_qty>0',
            'uvp.stock_qty>'.$cfgMinQty, 'uvp.stock_qty>ssi.min_qty'
        );

        if ((int)Mage::getStoreConfig(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_BACKORDERS)) {
            $_statusExpr = $adapter->getCheckSql(
                'uvp.backorders=-1 AND (ssi.use_config_backorders>0 OR ssi.backorders>0)'
                .' OR uvp.backorders>0 OR uvp.stock_qty IS NULL OR '.$stockQtyExpr,
                '1', '0'
            );
        } else {
            $_statusExpr = $adapter->getCheckSql(
                'uvp.backorders=-1 AND (ssi.use_config_backorders=0 AND ssi.backorders>0)'
                .' OR uvp.backorders>0 OR uvp.stock_qty IS NULL OR '.$stockQtyExpr,
                '1', '0'
            );
        }
        $statusExpr = sprintf('IF(MAX(%s)>0,1,0)',
            $adapter->getCheckSql('uvp.vendor_product_id IS NULL OR uv.vendor_id IS NULL', '0', $_statusExpr)
        );
        $select->columns(array('item_id' => 'cisi.item_id', 'qty' => new Zend_Db_Expr('0'), 'is_in_stock' => $statusExpr));

        if (!is_null($entityIds)) {
            $select->where('e.entity_id IN(?)', $entityIds);
        }

        $query = $select->insertFromSelect($this->getTable('cataloginventory/stock_item'), array('item_id', 'qty', 'is_in_stock'), true);
        $adapter->query($query);

        return $this;
    }
}
