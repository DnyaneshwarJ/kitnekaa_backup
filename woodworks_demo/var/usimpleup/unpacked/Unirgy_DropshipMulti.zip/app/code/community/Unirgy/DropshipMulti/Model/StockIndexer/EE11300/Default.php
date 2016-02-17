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
 * CatalogInventory Default Stock Status Indexer Resource Model
 *
 * @category    Mage
 * @package     Mage_CatalogInventory
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Unirgy_DropshipMulti_Model_StockIndexer_EE11300_Default
    extends Mage_Catalog_Model_Resource_Product_Indexer_Abstract
    implements Mage_CatalogInventory_Model_Resource_Indexer_Stock_Interface
{
    /**
     * Current Product Type Id
     *
     * @var string
     */
    protected $_typeId;

    /**
     * Product Type is composite flag
     *
     * @var bool
     */
    protected $_isComposite    = false;

    /**
     * Initialize connection and define main table name
     *
     */
    protected function _construct()
    {
        $this->_init('cataloginventory/stock_status', 'product_id');
    }

    /**
     * Reindex all stock status data for default logic product type
     *
     * @return Mage_CatalogInventory_Model_Resource_Indexer_Stock_Default
     */
    public function reindexAll()
    {
        $this->useIdxTable(true);
        $this->_prepareIndexTable();
        return $this;
    }

    /**
     * Reindex stock data for defined product ids
     *
     * @param int|array $entityIds
     * @return Mage_CatalogInventory_Model_Resource_Indexer_Stock_Default
     */
    public function reindexEntity($entityIds)
    {
        $this->_updateIndex($entityIds);
        return $this;
    }

    /**
     * Set active Product Type Id
     *
     * @param string $typeId
     * @return Mage_CatalogInventory_Model_Resource_Indexer_Stock_Default
     */
    public function setTypeId($typeId)
    {
        $this->_typeId = $typeId;
        return $this;
    }

    /**
     * Retrieve active Product Type Id
     *
     * @throws Mage_Core_Exception
     *
     * @return string
     */
    public function getTypeId()
    {
        if (is_null($this->_typeId)) {
            Mage::throwException(Mage::helper('udropship')->__('Undefined product type.'));
        }
        return $this->_typeId;
    }

    /**
     * Set Product Type Composite flag
     *
     * @param bool $flag
     * @return Mage_CatalogInventory_Model_Resource_Indexer_Stock_Default
     */
    public function setIsComposite($flag)
    {
        $this->_isComposite = (bool)$flag;
        return $this;
    }

    /**
     * Check product type is composite
     *
     * @return bool
     */
    public function getIsComposite()
    {
        return $this->_isComposite;
    }

    /**
     * Retrieve is Global Manage Stock enabled
     *
     * @return bool
     */
    protected function _isManageStock()
    {
        return Mage::getStoreConfigFlag(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MANAGE_STOCK);
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
        $isUdm = Mage::helper('udmulti')->isActive();
        $adapter = $this->_getWriteAdapter();
        $select  = $adapter->select()
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
            ->where('cw.website_id != 0')
            ->where('e.type_id = ?', $this->getTypeId());

        if ($isUdm) {
            $uvpColumns = $adapter->describeTable($this->getTable('udropship/vendor_product'));
            $uvpCond = 'uvp.product_id=e.entity_id';
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
            $select->group(array('e.entity_id','cw.website_id','cis.stock_id'));
            $_qtyExpr = $adapter->getCheckSql(
                'uvp.stock_qty IS NULL',
                '10000', $adapter->getCheckSql('uvp.stock_qty>0', 'uvp.stock_qty', '0')
            );
            $qtyExpr = sprintf('MAX(%s)',
                $adapter->getCheckSql('uvp.vendor_product_id IS NULL OR uv.vendor_id IS NULL', '0', $_qtyExpr)
            );

            $cfgMinQty = (int)Mage::getStoreConfig(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MIN_QTY);

            $stockQtyExpr = $adapter->getCheckSql(
                'cisi.use_config_min_qty>0',
                'uvp.stock_qty>'.$cfgMinQty, 'uvp.stock_qty>cisi.min_qty'
            );

            if ((int)Mage::getStoreConfig(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_BACKORDERS)) {
                $_statusExpr = $adapter->getCheckSql(
                    'uvp.backorders=-1 AND (cisi.use_config_backorders>0 OR cisi.backorders>0)'
                    .' OR uvp.backorders>0 OR uvp.stock_qty IS NULL OR '.$stockQtyExpr,
                    '1', '0'
                );
            } else {
                $_statusExpr = $adapter->getCheckSql(
                    'uvp.backorders=-1 AND (cisi.use_config_backorders=0 AND cisi.backorders>0)'
                    .' OR uvp.backorders>0 OR uvp.stock_qty IS NULL OR '.$stockQtyExpr,
                    '1', '0'
                );
            }
            $statusExpr = sprintf('MAX(%s)',
                $adapter->getCheckSql('uvp.vendor_product_id IS NULL OR uv.vendor_id IS NULL', '0', $_statusExpr)
            );
            $select->columns(array('qty' => $qtyExpr, 'status' => $statusExpr));
        } else {
            $qtyExpr = $adapter->getCheckSql('cisi.qty > 0', 'cisi.qty', '0');
            if ($this->_isManageStock()) {
                $statusExpr = $adapter->getCheckSql('cisi.use_config_manage_stock = 0 AND cisi.manage_stock = 0',
                    1, 'cisi.is_in_stock');
            } else {
                $statusExpr = $adapter->getCheckSql('cisi.use_config_manage_stock = 0 AND cisi.manage_stock = 1',
                    'cisi.is_in_stock', 1);
            }
            $select->columns(array('qty' => $qtyExpr, 'status' => $statusExpr));
        }

        // add limitation of status
        $condition = $adapter->quoteInto('=?', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
        $this->_addAttributeToSelect($select, 'status', 'e.entity_id', 'cs.store_id', $condition);

        if (!is_null($entityIds)) {
            $select->where('e.entity_id IN(?)', $entityIds);
        }

        return $select;
    }

    /**
     * Prepare stock status data in temporary index table
     *
     * @param int|array $entityIds  the product limitation
     * @return Mage_CatalogInventory_Model_Resource_Indexer_Stock_Default
     */
    protected function _prepareIndexTable($entityIds = null)
    {
        $adapter = $this->_getWriteAdapter();
        $this->_syncUdmWithStockItem($entityIds);
        $select  = $this->_getStockStatusSelect($entityIds);
        $query   = $select->insertFromSelect($this->getIdxTable());
        $adapter->query($query);

        return $this;
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

        $uvpColumns = $adapter->describeTable($this->getTable('udropship/vendor_product'));
        $uvpCond = 'uvp.product_id=e.entity_id';
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
            'cisi.use_config_min_qty>0',
            'uvp.stock_qty>'.$cfgMinQty, 'uvp.stock_qty>cisi.min_qty'
        );

        if ((int)Mage::getStoreConfig(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_BACKORDERS)) {
            $_statusExpr = $adapter->getCheckSql(
                'uvp.backorders=-1 AND (cisi.use_config_backorders>0 OR cisi.backorders>0)'
                .' OR uvp.backorders>0 OR uvp.stock_qty IS NULL OR '.$stockQtyExpr,
                '1', '0'
            );
        } else {
            $_statusExpr = $adapter->getCheckSql(
                'uvp.backorders=-1 AND (cisi.use_config_backorders=0 AND cisi.backorders>0)'
                .' OR uvp.backorders>0 OR uvp.stock_qty IS NULL OR '.$stockQtyExpr,
                '1', '0'
            );
        }
        $statusExpr = sprintf('MAX(%s)',
            $adapter->getCheckSql('uvp.vendor_product_id IS NULL OR uv.vendor_id IS NULL', '0', $_statusExpr)
        );
        $select->columns(array('item_id' => 'cisi.item_id', 'qty' => $qtyExpr, 'is_in_stock' => $statusExpr));

        if (!is_null($entityIds)) {
            $select->where('e.entity_id IN(?)', $entityIds);
        }

        $query = $select->insertFromSelect($this->getTable('cataloginventory/stock_item'), array('item_id', 'qty', 'is_in_stock'), true);
        $adapter->query($query);

        return $this;
    }

    /**
     * Update Stock status index by product ids
     *
     * @param array|int $entityIds
     * @return Mage_CatalogInventory_Model_Resource_Indexer_Stock_Default
     */
    protected function _updateIndex($entityIds)
    {
        $adapter = $this->_getWriteAdapter();
        $this->_syncUdmWithStockItem($entityIds);
        $select  = $this->_getStockStatusSelect($entityIds, true);
        $query   = $adapter->query($select);

        $i      = 0;
        $data   = array();
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $i ++;
            $data[] = array(
                'product_id'    => (int)$row['entity_id'],
                'website_id'    => (int)$row['website_id'],
                'stock_id'      => (int)$row['stock_id'],
                'qty'           => (float)$row['qty'],
                'stock_status'  => (int)$row['status'],
            );
            if (($i % 1000) == 0) {
                $this->_updateIndexTable($data);
                $data = array();
            }
        }
        $this->_updateIndexTable($data);

        return $this;
    }

    /**
     * Update stock status index table (INSERT ... ON DUPLICATE KEY UPDATE ...)
     *
     * @param array $data
     * @return Mage_CatalogInventory_Model_Resource_Indexer_Stock_Default
     */
    protected function _updateIndexTable($data)
    {
        if (empty($data)) {
            return $this;
        }

        $adapter = $this->_getWriteAdapter();
        $adapter->insertOnDuplicate($this->getMainTable(), $data, array('qty', 'stock_status'));

        return $this;
    }

    /**
     * Retrieve temporary index table name
     *
     * @param string $table
     * @return string
     */
    public function getIdxTable($table = null)
    {
        if ($this->useIdxTable()) {
            return $this->getTable('cataloginventory/stock_status_indexer_idx');
        }
        return $this->getTable('cataloginventory/stock_status_indexer_tmp');
    }
}
