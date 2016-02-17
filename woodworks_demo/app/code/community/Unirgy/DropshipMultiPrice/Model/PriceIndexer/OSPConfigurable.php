<?php
class Unirgy_DropshipMultiPrice_Model_PriceIndexer_OSPConfigurable
    extends Unirgy_DropshipMultiPrice_Model_PriceIndexer_CE1700_Configurable
{
    protected function _isManageStock()
    {
        return Mage::getStoreConfigFlag(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MANAGE_STOCK);
    }

    #Don't pay any attention to cost of specific conf product options, as SCP doesn't use them
    protected function _applyConfigurableOption()
    {
        return $this;
    }

    #This calculates final price using SCP logic: minimal child product finalprice
    #instead of the just the entered configurable price
    #It uses a subquery/group-by hack to ensure that the various column values are all from the row with the lowest final price.
    #See Kasey Speakman comment here: http://dev.mysql.com/doc/refman/5.1/en/example-maximum-column-group-row.html
    #It's all quite complicated. :/
    protected function _prepareFinalPriceData($entityIds = null)
    {
        $this->_prepareDefaultFinalPriceTable();

        $write  = $this->_getWriteAdapter();
        $select = $write->select()
            ->from(
                array('e' => $this->getTable('catalog/product')),
                array())
            ->joinLeft(
                array('l' => $this->getTable('catalog/product_super_link')),
                'l.parent_id = e.entity_id',
                array())
            ->join(
                array('ce' => $this->getTable('catalog/product')),
                'ce.entity_id = l.product_id',
                array())
            ->join(
                array('pi' => $this->getIdxTable()),
                'ce.entity_id = pi.entity_id',
                array())
            ->join(
                array('uvp'=>$this->getTable('udropship/vendor_product')),
                'uvp.product_id=pi.entity_id AND uvp.status>0 AND (uvp.stock_qty is null or uvp.stock_qty>0)',
                array())
            ->join(
                array('cw' => $this->getTable('core/website')),
                'pi.website_id = cw.website_id',
                array())
            ->join(
                array('csg' => $this->getTable('core/store_group')),
                'csg.website_id = cw.website_id AND cw.default_group_id = csg.group_id',
                array())
            ->join(
                array('cs' => $this->getTable('core/store')),
                'csg.default_store_id = cs.store_id AND cs.store_id != 0',
                array())
            ->join(
                array('cis' => $this->getTable('cataloginventory/stock')),
                '',
                array())
            ->joinLeft(
                array('cisi' => $this->getTable('cataloginventory/stock_item')),
                'cisi.stock_id = cis.stock_id AND cisi.product_id = ce.entity_id',
                array())
            ->where('e.type_id=?', $this->getTypeId()); ## is this one needed?


        $productStatusExpr  = $this->_addAttributeToSelect($select, 'status', 'ce.entity_id', 'cs.store_id');

        if ($this->_isManageStock()) {
            $stockStatusExpr = new Zend_Db_Expr('IF(cisi.use_config_manage_stock = 0 AND cisi.manage_stock = 0,' . ' 1, cisi.is_in_stock)');
        } else {
            $stockStatusExpr = new Zend_Db_Expr('IF(cisi.use_config_manage_stock = 0 AND cisi.manage_stock = 1,' . 'cisi.is_in_stock, 1)');
        }
        $isInStockExpr = new Zend_Db_Expr("IF({$stockStatusExpr}, 1, 0)");

        $isValidChildProductExpr = new Zend_Db_Expr("{$productStatusExpr}");

        $select->columns(array(
            'entity_id'         => new Zend_Db_Expr('e.entity_id'),
            'customer_group_id' => new Zend_Db_Expr('pi.customer_group_id'),
            'website_id'        => new Zend_Db_Expr('cw.website_id'),
            'tax_class_id'      => new Zend_Db_Expr('pi.tax_class_id'),
            'orig_price'        => new Zend_Db_Expr('pi.price'),
            'price'             => new Zend_Db_Expr('pi.final_price'),
            'min_price'         => new Zend_Db_Expr('pi.final_price'),
            'max_price'         => new Zend_Db_Expr('pi.final_price'),
            'tier_price'        => new Zend_Db_Expr('pi.tier_price'),
            'base_tier'         => new Zend_Db_Expr('pi.tier_price'),
            'group_price'       => new Zend_Db_Expr('pi.group_price'),
        ));

        $umpSrc = Mage::getSingleton('udmultiprice/source');
        $canStates = $umpSrc->setPath('vendor_product_state_canonic')->toOptionHash();
        foreach ($canStates as $csKey=>$csLbl) {
            $csMinKey = 'udmp_'.$csKey.'_min_price';
            $csMaxKey = 'udmp_'.$csKey.'_max_price';
            $csCntKey = 'udmp_'.$csKey.'_cnt';
            $extStates = $umpSrc->getCanonicExtStates($csKey);
            if (empty($extStates)) {
                $select->columns(array($csMinKey=>new Zend_Db_Expr('NULL')));
                $select->columns(array($csMaxKey=>new Zend_Db_Expr('NULL')));
                $select->columns(array($csCntKey=>new Zend_Db_Expr('NULL')));
                continue;
            }
            $csMinPriceSql = $write->getCheckSql("pi.$csMinKey is not null and $isInStockExpr", "pi.$csMinKey", "999999");
            $csMaxPriceSql = $write->getCheckSql("pi.$csMaxKey is not null and $isInStockExpr", "pi.$csMaxKey", "-999999");
            $csCntSql = $write->getCheckSql("pi.$csMinKey is not null and $isInStockExpr", "uvp.vendor_id", "NULL");
            $select->columns(array($csMinKey=>$csMinPriceSql));
            $select->columns(array($csMaxKey=>$csMaxPriceSql));
            $select->columns(array($csCntKey=>$csCntSql));
        }

        if (!is_null($entityIds)) {
            $select->where('e.entity_id IN(?)', $entityIds);
        }

        #Inner select order needs to be:
        #1st) If it's in stock come first (out of stock product prices aren't used if not-all products are out of stock)
        #2nd) Finalprice
        #3rd) $price, in case all finalPrices are NULL. (this gives the lowest price for all associated products when they're all out of stock)
        $sortExpr = new Zend_Db_Expr("${isInStockExpr} DESC, pi.final_price ASC, pi.price ASC");
        $select->order($sortExpr);

        $select->columns(array('child_id'=>'pi.entity_id'));
        //Mage::dumpToLog("$select", 'inner$select');
        //Mage::dumpToLog($select->getAdapter()->fetchAll($select), 'inner$select');

        /**
         * Add additional external limitation
         */
        Mage::dispatchEvent('prepare_catalog_product_index_select', array(
            'select'        => $select,
            'entity_field'  => new Zend_Db_Expr('e.entity_id'),
            'website_field' => new Zend_Db_Expr('cw.website_id'),
            'store_field'   => new Zend_Db_Expr('cs.store_id')
        ));


        #This uses the fact that mysql's 'group by' picks the first row, and the subselect is ordered as we want it
        #Bit hacky, but lots of people do it :)
        $outerSelect = $write->select()
            ->from(array("inner" => $select), 'entity_id')
            ->group(array('inner.entity_id', 'inner.customer_group_id', 'inner.website_id'));

        $outerSelect->columns(array(
            'customer_group_id',
            'website_id',
            'tax_class_id',
            'orig_price',
            'price',
            'min_price',
            'max_price'     => new Zend_Db_Expr('MAX(inner.max_price)'),
            'tier_price',
            'base_tier',
            'group_price',
            'base_group_price'=>'group_price',
            #'child_entity_id'
        ));
        $canStates = Mage::getSingleton('udmultiprice/source')
            ->setPath('vendor_product_state_canonic')
            ->toOptionHash();
        $csColumns = array();
        foreach ($canStates as $csKey=>$csLbl) {
            $csKey = 'udmp_'.$csKey;
            $csMinKey = 'inner.'.$csKey.'_min_price';
            $csMaxKey = 'inner.'.$csKey.'_max_price';
            $csCntKey = 'inner.'.$csKey.'_cnt';
            $csColumns[$csKey.'_min_price'] = sprintf('IF(MIN(%1$s)=999999,null,MIN(%1$s))', $csMinKey);
            $csColumns[$csKey.'_max_price'] = sprintf('IF(MAX(%1$s)=-999999,null,MAX(%1$s))', $csMaxKey);
            $csColumns[$csKey.'_cnt'] = sprintf('IF(COUNT(DISTINCT %1$s)=0,null,COUNT(DISTINCT %1$s))', $csCntKey);
        }
        $outerSelect->columns($csColumns);

        //Mage::dumpToLog("$outerSelect", '$outerSelect');

        $query = $outerSelect->insertFromSelect($this->_getDefaultFinalPriceTable());
        $write->query($query);
        #Mage::log("SCP Price inner query: " . $select->__toString());
        #Mage::log("SCP Price outer query: " . $outerSelect->__toString());

        return $this;
    }
    protected function _movePriceDataToIndexTable()
    {
        return parent::_movePriceDataToIndexTable();
        $columns = array(
            'entity_id'         => 'entity_id',
            'customer_group_id' => 'customer_group_id',
            'website_id'        => 'website_id',
            'tax_class_id'      => 'tax_class_id',
            'price'             => 'orig_price',
            'final_price'       => 'price',
            'min_price'         => 'min_price',
            'max_price'         => 'max_price',
            'tier_price'        => 'tier_price',
            'group_price'       => 'group_price',
        );

        $write  = $this->_getWriteAdapter();
        $table  = $this->_getDefaultFinalPriceTable();
        $select = $write->select()
            ->from($table, $columns);

        $query = $select->insertFromSelect($this->getIdxTable(), array(), true);
        $write->query($query);

        /*
        if ($this->useIdxTable() && $this->_allowTableChanges) {
            $write->truncateTable($table);
        } else {
            $write->delete($table);
        }
        */

        return $this;
    }
}
