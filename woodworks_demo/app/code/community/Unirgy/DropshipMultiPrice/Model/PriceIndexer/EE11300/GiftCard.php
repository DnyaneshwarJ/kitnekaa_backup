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
 * @category    Enterprise
 * @package     Enterprise_GiftCard
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */


/**
 * GiftCard product price indexer resource model
 *
 * @category    Enterprise
 * @package     Enterprise_GiftCard
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Unirgy_DropshipMultiPrice_Model_PriceIndexer_EE11300_GiftCard extends Unirgy_DropshipMultiPrice_Model_PriceIndexer_CE1700_Default
{
    /**
     * Register data required by product type process in event object
     *
     * @param Mage_Index_Model_Event $event
     */
    public function registerEvent(Mage_Index_Model_Event $event)
    {
        $attributes = array(
            'allow_open_amount',
            'open_amount_min',
            'open_amount_max',
        );

        $entity = $event->getEntity();
        if ($entity == Mage_Catalog_Model_Product::ENTITY) {
            switch ($event->getType()) {
                case Mage_Index_Model_Event::TYPE_SAVE:
                    /* @var $product Mage_Catalog_Model_Product */
                    $product      = $event->getDataObject();
                    $reindexPrice = $product->getAmountsHasChanged();
                    foreach ($attributes as $code) {
                        if ($product->dataHasChangedFor($code)) {
                            $reindexPrice = true;
                            break;
                        }
                    }

                    if ($reindexPrice) {
                        $event->addNewData('product_type_id', $product->getTypeId());
                        $event->addNewData('reindex_price', 1);
                    }

                    break;

                case Mage_Index_Model_Event::TYPE_MASS_ACTION:
                    /* @var $actionObject Varien_Object */
                    $actionObject = $event->getDataObject();
                    $reindexPrice = false;

                    // check if attributes changed
                    $attrData = $actionObject->getAttributesData();
                    if (is_array($attrData)) {
                        foreach ($attributes as $code) {
                            if (array_key_exists($code, $attrData)) {
                                $reindexPrice = true;
                                break;
                            }
                        }
                    }

                    if ($reindexPrice) {
                        $event->addNewData('reindex_price_product_ids', $actionObject->getProductIds());
                    }

                    break;
            }
        }
    }

    /**
     * Prepare giftCard products prices in temporary index table
     *
     * @param int|array $entityIds  the entity ids limitation
     * @return Enterprise_GiftCard_Model_Resource_Indexer_Price
     */
    protected function _prepareFinalPriceData($entityIds = null)
    {
        $this->_prepareDefaultFinalPriceTable();

        $write  = $this->_getWriteAdapter();
        $select = $write->select()
            ->from(array('e' => $this->getTable('catalog/product')), array('entity_id'))
            ->joinLeft(
                array('uvp'=>$this->getTable('udropship/vendor_product')),
                'uvp.product_id=e.entity_id AND uvp.status>0',
                array())
            ->joinLeft(
                array('uv'=>$this->getTable('udropship/vendor')),
                'uv.vendor_id=uvp.vendor_id AND uv.status=\'A\'',
                array())
            ->join(
                array('cg' => $this->getTable('customer/customer_group')),
                '',
                array('customer_group_id')
            );
        $this->_addWebsiteJoinToSelect($select, true);
        $this->_addProductWebsiteJoinToSelect($select, 'cw.website_id', 'e.entity_id');
        $select->columns(array('website_id'), 'cw')
            ->columns(array('tax_class_id'  => new Zend_Db_Expr('0')))
            ->where('e.type_id = ?', $this->getTypeId());

        // add enable products limitation
        $statusCond = $write->quoteInto('=?', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
        $this->_addAttributeToSelect($select, 'status', 'e.entity_id', 'cs.store_id', $statusCond, true);

        $allowOpenAmount = $this->_addAttributeToSelect($select, 'allow_open_amount', 'e.entity_id', 'cs.store_id');
        $openAmountMin    = $this->_addAttributeToSelect($select, 'open_amount_min', 'e.entity_id', 'cs.store_id');
//        $openAmounMax    = $this->_addAttributeToSelect($select, 'open_amount_max', 'e.entity_id', 'cs.store_id');



        $attrAmounts = $this->_getAttribute('giftcard_amounts');
        // join giftCard amounts table
        $select->joinLeft(
            array('gca' => $this->getTable('enterprise_giftcard/amount')),
            'gca.entity_id = e.entity_id AND gca.attribute_id = '
            . $attrAmounts->getAttributeId()
            . ' AND (gca.website_id = cw.website_id OR gca.website_id = 0)',
            array()
        );

        $amountsExpr    = 'MIN(' . $write->getCheckSql('gca.value_id IS NULL', 'NULL', 'gca.value') . ')';

        $openAmountExpr = 'MIN(' . $write->getCheckSql(
                $allowOpenAmount . ' = 1',
                $write->getCheckSql($openAmountMin . ' > 0', $openAmountMin, '0'),
                'NULL'
            ) . ')';

        $priceExpr = new Zend_Db_Expr(
            'ROUND(' . $write->getCheckSql(
                $openAmountExpr . ' IS NULL',
                $write->getCheckSql($amountsExpr . ' IS NULL', '0', $amountsExpr),
                $write->getCheckSql(
                    $amountsExpr . ' IS NULL',
                    $openAmountExpr,
                    $write->getCheckSql(
                        $openAmountExpr . ' > ' . $amountsExpr,
                        $amountsExpr,
                        $openAmountExpr
                    )
                )
            ) . ', 4)'
        );

        $select->group(array('e.entity_id', 'cg.customer_group_id', 'cw.website_id'))
            ->columns(array(
                'price'            => new Zend_Db_Expr('NULL'),
                'final_price'      => $priceExpr,
                'min_price'        => $priceExpr,
                'max_price'        => new Zend_Db_Expr('NULL'),
                'tier_price'       => new Zend_Db_Expr('NULL'),
                'base_tier'        => new Zend_Db_Expr('NULL'),
                'group_price'      => new Zend_Db_Expr('NULL'),
                'base_group_price' => new Zend_Db_Expr('NULL'),
            ));

        $umpSrc = Mage::getSingleton('udmultiprice/source');
        $canStates = $umpSrc->setPath('vendor_product_state_canonic')->toOptionHash();

        /*
        $uvPrice        = 'uvp.vendor_price';
        $uvSpecialPrice = 'uvp.special_price';
        $uvSpecialFrom  = 'uvp.special_from_date';
        $uvSpecialTo    = 'uvp.special_to_date';

        $uvSpecialFromDate    = $write->getDatePartSql($uvSpecialFrom);
        $uvSpecialToDate      = $write->getDatePartSql($uvSpecialTo);

        $uvSpecialFromUse     = $write->getCheckSql("{$uvSpecialFromDate} <= {$currentDate}", '1', '0');
        $uvSpecialToUse       = $write->getCheckSql("{$uvSpecialToDate} >= {$currentDate}", '1', '0');
        $uvSpecialFromHas     = $write->getCheckSql("{$uvSpecialFrom} IS NULL", '1', "{$uvSpecialFromUse}");
        $uvSpecialToHas       = $write->getCheckSql("{$uvSpecialTo} IS NULL", '1', "{$uvSpecialToUse}");
        $uvFinalPrice         = $write->getCheckSql("{$uvSpecialFromHas} > 0 AND {$uvSpecialToHas} > 0"
        . " AND {$uvSpecialPrice} < {$uvPrice}", $uvSpecialPrice, $uvPrice);
        */

        $csPrice = $priceExpr;
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
            $extStatesSql = sprintf("'%s'", implode(',', $extStates));
            $csCaseResults = $csCaseResultsCnt = array();
            foreach ($extStates as $extState) {
                $csCaseResults["'$extState'"] = $csPrice;
                $csCaseResultsCnt["'$extState'"] = 1;
            }
            $csMinPriceSql = sprintf('IF(FIND_IN_SET(uvp.state,%s),%s,999999)', $extStatesSql, $csPrice);
            $csMaxPriceSql = sprintf('IF(FIND_IN_SET(uvp.state,%s),%s,-999999)', $extStatesSql, $csPrice);
            $csCntSql = sprintf('IF(FIND_IN_SET(uvp.state,%s),1,0)', $extStatesSql);
            //$csMinPrice = sprintf('IF(MIN(%1$s)=999999,null,MIN(%1$s))', $csMinPriceSql);
            //$csMaxPrice = sprintf('IF(MAX(%1$s)=-999999,null,MAX(%1$s))', $csMaxPriceSql);
            $csCnt = sprintf('IF(SUM(%1$s)=0,null,SUM(%1$s))', $csCntSql);
            $select->columns(array($csMinKey=>new Zend_Db_Expr('NULL')));
            $select->columns(array($csMaxKey=>new Zend_Db_Expr('NULL')));
            $select->columns(array($csCntKey=>new Zend_Db_Expr('NULL')));
        }

        $columns = $select->getPart(Zend_Db_Select::COLUMNS);
        foreach ($columns as &$column) {
            if (@$column[2] == 'min_price') {
                $column[1] = new Zend_Db_Expr(sprintf('%s', $csPrice));
            } elseif (@$column[2] == 'max_price') {
                $column[1] = new Zend_Db_Expr(sprintf('%s', $csPrice));
            }
        }
        unset($column);
        $select->setPart(Zend_Db_Select::COLUMNS, $columns);

        if (!is_null($entityIds)) {
            $select->where('e.entity_id IN(?)', $entityIds);
        }

        /**
         * Add additional external limitation
         */
        Mage::dispatchEvent('prepare_catalog_product_index_select', array(
            'select'        => $select,
            'entity_field'  => new Zend_Db_Expr('e.entity_id'),
            'website_field' => new Zend_Db_Expr('cw.website_id'),
            'store_field'   => new Zend_Db_Expr('cs.store_id')
        ));

        $query = $select->insertFromSelect($this->_getDefaultFinalPriceTable());
        $write->query($query);

        return $this;
    }
}
