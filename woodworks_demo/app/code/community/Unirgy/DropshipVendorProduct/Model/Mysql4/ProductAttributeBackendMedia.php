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

class Unirgy_DropshipVendorProduct_Model_Mysql4_ProductAttributeBackendMedia extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Attribute_Backend_Media
{
    protected $_eventPrefix = 'catalog_product_attribute_backend_media';
    public function loadGallery($product, $object)
    {
        $eventObjectWrapper = new Varien_Object(
            array(
                'product' => $product,
                'backend_attribute' => $object
            )
        );
        Mage::dispatchEvent(
            $this->_eventPrefix . '_load_gallery_before',
            array('event_object_wrapper' => $eventObjectWrapper)
        );

        if ($eventObjectWrapper->hasProductIdsOverride()) {
            $productIds = $eventObjectWrapper->getProductIdsOverride();
        } else {
            $productIds = array($product->getId());
        }
        // Select gallery images for product
        $select = $this->_getReadAdapter()->select()
            ->from(
                array('main'=>$this->getMainTable()),
                array('value_id', 'super_attribute', 'value AS file', 'product_id' => 'entity_id')
            )
            ->joinLeft(
                array('value'=>$this->getTable(self::GALLERY_VALUE_TABLE)),
                'main.value_id=value.value_id AND value.store_id='.(int)$product->getStoreId(),
                array('label','position','disabled')
            )
            ->joinLeft( // Joining default values
                array('default_value'=>$this->getTable(self::GALLERY_VALUE_TABLE)),
                'main.value_id=default_value.value_id AND default_value.store_id=0',
                array(
                    'label_default' => 'label',
                    'position_default' => 'position',
                    'disabled_default' => 'disabled'
                )
            )
            ->where('main.attribute_id = ?', $object->getAttribute()->getId())
            ->where('main.entity_id in (?)', $productIds)
            ->order('IF(value.position IS NULL, default_value.position, value.position) ASC');

        if ($product->getTypeId() == 'configurable'
            && $product->getIsProductListFlag()
            && is_callable(array($product->getTypeInstance(true), 'getConfigurableAttributes'))
        ) {
            $cfgAttrs = $product->getTypeInstance(true)->getConfigurableAttributes($product);
            $usedProds = $product->getTypeInstance(true)->getUsedProductIds($product);
            if (($cfgAttr = $cfgAttrs->getFirstItem())) {
                $prices = $cfgAttr->getPrices();
                $filterSql = array();
                if (!empty($prices)) {
                    foreach ($prices as $pr) {
                        $filterSql[] = $this->_getReadAdapter()->quoteInto("main.super_attribute like ?",
                            '%"'.$cfgAttr->getAttributeId().'":"'.@$pr['value_index'].'"%'
                        );
                    }
                }
                if (empty($filterSql) || empty($prices)) {
                    $select->where('false');
                } else {
                    $select->where(implode(' OR ', $filterSql));
                }
                //$select->limit(count($usedProds));
            } else {
                $select->where('false');
            }
        } elseif ($product->getTypeId() == 'configurable' && $product->getIsQuoteListFlag()) {
            if (($attributesOption = $product->getCustomOption('attributes'))
                && ($cfgSelAttrs = unserialize($attributesOption->getValue()))
                && is_array($cfgSelAttrs)
            ) {
                $filterSql = array();
                foreach ($cfgSelAttrs as $cfgSelAttrId => $cfgSelAttrVal) {
                    $filterSql[] = $this->_getReadAdapter()->quoteInto("main.super_attribute like ?",
                        '%"'.$cfgSelAttrId.'":"'.$cfgSelAttrVal.'"%'
                    );
                }
                if (empty($filterSql)) {
                    $select->where('false');
                } else {
                    $select->where(implode(' AND ', $filterSql));
                }
                $select->limit(1);
            }
        }

        $_result = $this->_getReadAdapter()->fetchAll($select);
        $result = array();
        if ($product->getTypeId() == 'configurable'
            && is_callable(array($product->getTypeInstance(true), 'getConfigurableAttributes'))
        ) {
            $cfgAttrs = $product->getTypeInstance(true)->getConfigurableAttributes($product);
            $__cfgAttr = null;
            $product->getIsProductListFlag() && ($__cfgAttr = $cfgAttrs->getFirstItem());
            foreach ($_result as &$row) {
                try {
                    $jsSA = array();
                    $jsSA = Mage::helper('core')->jsonDecode(@$row['super_attribute']);
                } catch (Exception $e) {}
                if (empty($row['super_attribute']) || !is_array($jsSA)) {
                    $row['super_attribute'] = array();
                } else {
                    $row['super_attribute'] = $jsSA;
                }
                foreach ($cfgAttrs as $cfgAttr) {
                    if ($cfgAttr->getIdentifyImage() && !isset($row['super_attribute'][$cfgAttr->getAttributeId()])) {
                        $row['super_attribute'][$cfgAttr->getAttributeId()] = '';
                    }
                }
                if ($__cfgAttr) {
                    $fKey = sprintf('%s:%s', $__cfgAttr->getAttributeId(), @$row['super_attribute'][$__cfgAttr->getAttributeId()]);
                    if (empty($result[$fKey])) {
                        $result[$fKey] = $row;
                    }
                } else {
                    $result[] = $row;
                }
            }
            unset($row);
        } else {
            foreach ($_result as &$row) {
                try {
                    $jsSA = array();
                    $jsSA = Mage::helper('core')->jsonDecode(@$row['super_attribute']);
                } catch (Exception $e) {}
                if (empty($row['super_attribute']) || !is_array($jsSA)) {
                    $row['super_attribute'] = array();
                } else {
                    $row['super_attribute'] = $jsSA;
                }
                $result[] = $row;
            }
            unset($row);
        }
        $this->_removeDuplicates($result);
        return $result;
    }

    public function updateGallery($data, $valueId)
    {
        $w = $this->_getWriteAdapter();
        $w->update($this->getMainTable(), $data, $w->quoteInto('value_id=?', $valueId));
        return $this;
    }
}