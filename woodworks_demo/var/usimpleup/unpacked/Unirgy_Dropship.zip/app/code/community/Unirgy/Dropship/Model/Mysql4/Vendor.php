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

class Unirgy_Dropship_Model_Mysql4_Vendor extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('udropship/vendor', 'vendor_id');
    }

    protected $_directFields;
    public function getDirectFields()
    {
        if (null === $this->_directFields) {
            $tblColumns = $this->_getReadAdapter()->describeTable($this->getMainTable());
            foreach ($tblColumns as $tblColumn) {
                $this->_directFields[] = $tblColumn['COLUMN_NAME'];
            }
        }
        return $this->_directFields;
    }

    public function getShippingMethodCode($vendor, $carrierCode, $method)
    {
        $read = $this->_getReadAdapter();

        $select = $read->select()
            ->from(array('vs'=>$this->getTable('vendor_shipping')), array('carrier_code'))
            ->where('vendor_id=?', $vendor->getId());

        $vcCode = $read->fetchOne($select);
        if ($vcCode) {
            $carrierCode = $vcCode;
        }

        $select = $read->select()
            ->from(array('s'=>$this->getTable('shipping')), array())
            ->join(array('sm'=>$this->getTable('shipping_method')), 'sm.shipping_id=s.shipping_id', array('method_code'))
            ->where('sm.carrier_code=?', $carrierCode)
            ->where('s.shipping_code=?', $method)
        ;
        return $read->fetchOne($select);

    }

    public function getShippingMethods($vendor, $system=false)
    {
        $read = $this->_getReadAdapter();
        $prefCarrierSql = new Zend_Db_Expr("'{$vendor->getCarrierCode()}'");
        $estCarrierSql = new Zend_Db_Expr("IFNULL(vs.est_carrier_code,$prefCarrierSql)");
        $carrierSql = new Zend_Db_Expr("IF(vs.carrier_code='**estimate**',$estCarrierSql,IFNULL(vs.carrier_code,$prefCarrierSql))");
        $select = $read->select()
            ->from(array('vs'=>$this->getTable('vendor_shipping')), array())
            ->join(array('sm'=>$this->getTable('shipping_method')), "sm.shipping_id=vs.shipping_id and sm.carrier_code=$carrierSql", array('method_code'))
            ->where('vs.vendor_id=?', $vendor->getId())
            ->columns(array(
                'vendor_shipping_id', 'vendor_id', 'shipping_id', 'account_id',
                'price_type', 'price', 'priority', 'handling_fee', 'est_carrier_code',
                'ovrd_carrier_code' => 'vs.carrier_code', 'carrier_code'=>$carrierSql,
                'allow_extra_charge','extra_charge_suffix','extra_charge_type','extra_charge'
            ), 'vs')
        ;
        $rows = $read->fetchAll($select);

        $methods = array();
        foreach ($rows as $r) {
            $methods[$r['shipping_id']][] = $r;
        }
        return $methods;
    }

    protected function _getAssociatedProductIdsSelect($vendor, $productIds=array())
    {
        $read = $this->_getReadAdapter();
        $vId = is_scalar($vendor) ? $vendor : $vendor->getId();
        $select = $read->select()
            ->from(array('vpassoc'=>$this->getTable('vendor_product_assoc')), array('vpassoc.product_id'))
            ->where('vpassoc.vendor_id=?', $vId);
        if ($productIds) {
            $select->where('vpassoc.product_id in (?)', $productIds);
        }
        return $select;
    }

    public function getAssociatedProductIdsSelect($vendor, $productIds=array())
    {
        $read = $this->_getReadAdapter();
        $select = $this->_getAssociatedProductIdsSelect($vendor, $productIds);
        if (!Mage::helper('udropship')->isUdmultiAvailable()) {
            $select->where('vpassoc.is_attribute=1');
        }
        return $select;
    }
    public function getAssociatedProductIds($vendor, $productIds=array())
    {
        $read = $this->_getReadAdapter();
        $select = $this->getAssociatedProductIdsSelect($vendor, $productIds);
        return $read->fetchCol($select);
    }
    public function getVendorTableProductIdsSelect($vendor, $productIds=array())
    {
        $read = $this->_getReadAdapter();
        $select = $this->_getAssociatedProductIdsSelect($vendor, $productIds);
        $select->where('vpassoc.is_udmulti=1');
        return $select;
    }
    public function getVendorTableProductIds($vendor, $productIds=array())
    {
        $read = $this->_getReadAdapter();
        $select = $this->getVendorTableProductIdsSelect($vendor, $productIds);
        return $read->fetchCol($select);
    }
    public function getVendorAttributeProductIdsSelect($vendor, $productIds=array())
    {
        $read = $this->_getReadAdapter();
        $select = $this->_getAssociatedProductIdsSelect($vendor, $productIds);
        $select->where('vpassoc.is_attribute=1');
        return $select;
    }
    public function getVendorAttributeProductIds($vendor, $productIds=array())
    {
        $read = $this->_getReadAdapter();
        $select = $this->getVendorAttributeProductIdsSelect($vendor, $productIds);
        return $read->fetchCol($select);
    }

    public function getAssociatedProducts($vendor, $productIds=array())
    {
        $products = array();
        if (Mage::helper('udropship')->isUdmultiAvailable()) {
            $products = $this->getVendorTableProducts($vendor, $productIds);
        }
        $products = $products + $this->getVendorAttributeProducts($vendor, $productIds);
        return $products;
    }

    public function getVendorTableProducts($vendor, $productIds=array())
    {
        $products = array();
        $read = $this->_getReadAdapter();
        $select = $read->select()
            ->from($this->getTable('udropship/vendor_product'))
            ->where('vendor_id=?', $vendor->getId());
        if ($productIds) {
            $select->where('product_id in (?)', $productIds);
        }
        $rows = $read->fetchAll($select);
        foreach ($rows as $r) {
            $products[$r['product_id']] = $r;
            $fields = array(
                'vendor_cost' => 1,
                'backorders' => 1,
                'vendor_title' => 0,
                'vendor_price' => 1,
                'shipping_price' => 1,
                'stock_qty' => 1,
                'state' => 0,
                'status' => 0,
                'avail_state' => 0,
                'avail_date' => 0,
                'special_price' => 1,
                'special_from_date' => 0,
                'special_to_date' => 0,
                'reserved_qty' => 1,
            );
            foreach ($fields as $_field=>$isNumeric) {
                if (array_key_exists($_field, $r) && !is_null($r[$_field])) {
                    if ($isNumeric) {
                        $r[$_field] = 1*$r[$_field];
                    }
                } else {
                    $r[$_field] = null;
                }
            }
            $products[$r['product_id']] = $r;
        }
        return $products;
    }

    public function getVendorAttributeProducts($vendor, $productIds=array())
    {
        $products = array();

        $read = $this->_getReadAdapter();

        $attr = Mage::getSingleton('eav/config')->getAttribute('catalog_product', 'udropship_vendor');
        $table = $attr->getBackend()->getTable();
        $select = $read->select()
            ->from($table, array('entity_id'))
            ->where('attribute_id=?', $attr->getId())
            ->where('value=?', $vendor->getId());
        if ($productIds) {
            $select->where('entity_id in (?)', $productIds);
        }
        if ($products) {
            $select->where('entity_id not in (?)', array_keys($products));
        }
        $rows = $read->fetchCol($select);
        foreach ($rows as $id) {
            $products[$id] = array();
        }

        return $products;
    }

    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        parent::_afterLoad($object);

        if ($object->getPasswordEnc()) {
            $object->setPassword(Mage::helper('core')->decrypt($object->getPasswordEnc()));
            $object->setPasswordEnc('');
        }
    }

    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        parent::_beforeSave($object);

        if ($object->getPassword()) {
            $object->setPasswordHash(Mage::helper('core')->getHash($object->getPassword(), 2));
            $object->setPassword('');
            $object->setPasswordEnc('');
        }
    }

    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        $this->_saveVendorProducts($object);
        $this->_saveVendorShipping($object);
        $this->enableDisableVendorAction($object);
        return parent::_afterSave($object);
    }

    protected function _beforeDelete(Mage_Core_Model_Abstract $object)
    {
        if ($object->getId() == Mage::helper('udropship')->getLocalVendorId()) {
            Mage::throwException(
                Mage::helper('udropship')->__('Cannot delete local vendor. Please change "Configuration / Drop Shipping / Vendor Options / Local Vendor" before')
            );
        }
        $this->resetVendorProducts($object);
        return parent::_beforeDelete($object);
    }

    public function resetVendorProducts($vendor)
    {
        $write = $this->_getWriteAdapter();

        $localVendorId = Mage::helper('udropship')->getLocalVendorId();

        if (!Mage::helper('udropship')->isUdmultiAvailable()
            && $localVendorId != $vendor->getId()
        ) {
            switch (Mage::getStoreConfig('udropship/customer/vendor_delete_action')) {
                case 'assign_local_enabled':
                    $this->_resetVendorProducts($vendor);
                    break;
                case 'assign_local_disable':
                    $this->_enableDisableVendorProducts($vendor, Mage_Catalog_Model_Product_Status::STATUS_DISABLED);
                    $this->_resetVendorProducts($vendor);
                    break;
                case 'delete':
                    if (($assocPids = $this->getVendorAttributeProducts($vendor))) {
                        $assocPids = array_keys($assocPids);
                        foreach ($assocPids as $productId) {
                            Mage::getSingleton('catalog/product')->load($productId)->delete();
                        }
                    }
                    break;
            }
        }

    }

    protected function _resetVendorProducts($vendor, $newVendor=null)
    {
        $write = $this->_getWriteAdapter();

        if ($newVendor == null) {
            $newVendor = Mage::helper('udropship')->getLocalVendorId();
        }

        if (($newVendor = Mage::helper('udropship')->getVendor($newVendor))
            && ($newVendorId = $newVendor->getId())
        ) {
            $attr = Mage::getSingleton('eav/config')->getAttribute('catalog_product', 'udropship_vendor');
            $where = 'attribute_id='.$attr->getId().' and value='.$vendor->getId();
            $write->update($attr->getBackend()->getTable(), array('value'=>$newVendorId), $where);
        }

        return $this;
    }

    protected function _saveVendorProducts($vendor)
    {
        // new category-product relationships
        $products = $vendor->getPostedProducts();
        if (is_null($products)) {
            return $this;
        }

        $attr = Mage::getSingleton('eav/config')->getAttribute('catalog_product', 'udropship_vendor');
        $isMulti = Mage::helper('udropship')->isUdmultiAvailable();
        $isMultiPrice = Mage::helper('udropship')->isUdmultiPriceAvailable();
        $vId = $vendor->getId();
        $localVendorId = Mage::helper('udropship')->getLocalVendorId();
        $table = $this->getTable('vendor_product');
        $write = $this->_getWriteAdapter();
        $hlp = Mage::helper('udropship');
        $rHlp = Mage::getResourceSingleton('udropship/helper');

        // retrieve old data for updated products
        if (empty($products)) {
            $old = array();
        } else {
            $old = $vendor->getAssociatedProducts(array_keys($products));
        }

        $insert = array();
        $delete = array();
        $delAttr = array();

        $_availDate = Mage::app()->getLocale()->date();
        $_dateFormat = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        $_inDateFormat = Varien_Date::DATETIME_INTERNAL_FORMAT;

        $dateFields = array('avail_date','special_from_date','special_to_date');

        foreach ($products as $id=>$a) {
            $a['product_id']=$id;
            $a['vendor_id']=$vId;
            if (!(int)$id) {
                continue;
            }
            // delete
            if (isset($a['on']) && $a['on']===false) {
                if (!empty($old[$id]['vendor_product_id'])) { // multi
                    $delete[] = $old[$id]['vendor_product_id'];
                } elseif (isset($old[$id])) { // attr
                    $delAttr[] = $id;
                }
                continue;
            }
            // not needed any more
            unset($a['on']);
            // empty value means NULL
            foreach ($a as $k=>$v) {
                if ($v==='') {
                    $a[$k] = null;
                }
                if (in_array($k, $dateFields) && !empty($a[$k])) {
                    $a[$k] = $hlp->dateLocaleToInternal($a[$k]);
                }
            }
            // insert
            if (!isset($old[$id]) || $isMulti && empty($old[$id]['vendor_product_id'])) {
                if ($isMulti) {
                    if (!array_key_exists('status', $a)) {
                        $a['status'] = Mage::helper('udmulti')->getDefaultMvStatus();
                    }
                    $insert[] = $write->quoteInto('(?)',
                        $rHlp->myPrepareDataForTable($table, $a, true)
                    );
                } else {
                    $insert[] = '('.$attr->getEntityTypeId().', '.$attr->getAttributeId().', '.(int)$id.', '.(int)$vId.')';
                }
            }
            // update
            elseif (!empty($old[$id]['vendor_product_id'])) {
                if (isset($a['stock_qty']) && !empty($a['use_reserved_qty'])) {
                    $a['stock_qty'] -= @$old[$id]['reserved_qty'];
                }
                if (!array_key_exists('stock_qty', $a) && !empty($a['use_reserved_qty'])) {
                    $a['stock_qty'] = @$old[$id]['stock_qty'] - @$old[$id]['reserved_qty'];
                }
                $a = Mage::getResourceSingleton('udropship/helper')->myPrepareDataForTable($table, $a);
                unset($a['vendor_product_id'], $a['vendor_id'], $a['product_id'], $a['use_reserved_qty'], $a['reserved_qty']); //security
                if (!empty($a)) {
                    $write->update($table, $a, 'vendor_product_id='.(int)$old[$id]['vendor_product_id']);
                }
            }
        }
        if ($insert) {
            if ($isMulti) {
                $write->query(sprintf("replace into %s (%s) values %s",
                    $table,
                    implode(',', array_keys($rHlp->myPrepareDataForTable($table, array(), true))),
                    join(',', $insert)
                ));
            } else {
                $write->query("replace into {$attr->getBackendTable()} (entity_type_id, attribute_id, entity_id, value) values ".join(',', $insert));
            }
        }
        if ($delete) {
            $write->delete($table, $write->quoteInto('vendor_product_id in (?)', $delete).$write->quoteInto(' AND vendor_id=?', $vId));
        }
        if ($delAttr) {
            $delAttrUpdateSql = sprintf('update %s set value=%s where entity_id in (%s) and attribute_id=%s',
                $attr->getBackendTable(), new Zend_Db_Expr('NULL'),
                $write->quote($delAttr), $write->quote($attr->getAttributeId())
            );
            $write->query($delAttrUpdateSql);
        }

        Mage::helper('udropship')->saveThisVendorProducts($products, $vendor);
        if ($isMulti) {
            foreach ($products as $_pId => $_p) {
                if (!(int)$_pId) {
                    continue;
                }
                if (!in_array($_pId, $delAttr)) {
                    Mage::helper('udmulti')->getUdmultiStock($_pId, true);
                    Mage::getModel('cataloginventory/stock_item')
                        ->loadByProduct($_pId)
                        ->setData('__dummy',1)
                        ->save();
                }
            }
        }

        if (Mage::helper('udropship')->hasMageFeature('indexer_1.4')) {
            $pIds = array_keys($products);
            if (!empty($pIds)) {
                $vendor->setData('__reindex_product_ids', $pIds);
            }
        }
        $vendor->unsetData('associated_products');
        $vendor->unsetData('posted_products');
    }

    public function enableDisableVendorAction($vendor)
    {
        $write = $this->_getWriteAdapter();
        if (!Mage::helper('udropship')->isUdmultiAvailable()
            && $vendor->dataHasChangedFor('status')
        ) {
            switch (Mage::getStoreConfig('udropship/customer/vendor_enable_disable_action')) {
                case 'enable_disable':
                    switch ($vendor->getStatus()) {
                        case Unirgy_Dropship_Model_Source::VENDOR_STATUS_INACTIVE:
                        case Unirgy_Dropship_Model_Source::VENDOR_STATUS_DISABLED:
                            $prodStatus = Mage_Catalog_Model_Product_Status::STATUS_DISABLED;
                            break;
                        case Unirgy_Dropship_Model_Source::VENDOR_STATUS_ACTIVE:
                            $prodStatus = Mage_Catalog_Model_Product_Status::STATUS_ENABLED;
                            break;
                    }
                    $this->_enableDisableVendorProducts($vendor, $prodStatus);
                    break;
            }
        }
        return $this;
    }
    protected function _enableDisableVendorProducts($vendor, $prodStatus)
    {
        $write = $this->_getWriteAdapter();
        if (!$vendor instanceof Unirgy_Dropship_Model_Vendor) {
            $vendor = Mage::helper('udropship')->getVendor($vendor);
        }
        if (($assocPids = $this->getVendorAttributeProducts($vendor))) {
            $assocPids = array_keys($assocPids);
            $pStAttr = Mage::getSingleton('eav/config')->getAttribute('catalog_product', 'status');
            $pStUpdateSql = sprintf('update %s set value=%s where entity_id in (%s) and attribute_id=%s',
                $pStAttr->getBackendTable(), $write->quote($prodStatus),
                $write->quote($assocPids), $write->quote($pStAttr->getAttributeId())
            );
            $write->query($pStUpdateSql);
            if (Mage::helper('udropship')->hasMageFeature('indexer_1.4')) {
                Mage::getSingleton('index/indexer')->processEntityAction(
                    Mage::getModel('catalog/product_action')->setProductIds($assocPids)->setAttributesData(array('status'=>$prodStatus)),
                    Mage_Catalog_Model_Product::ENTITY, Mage_Index_Model_Event::TYPE_MASS_ACTION
                );
            }
        }
        return $this;
    }

    public function _saveVendorShipping($vendor)
    {
        /**
         * new category-product relationships
         */
        $shipping = $vendor->getPostedShipping();
        if (is_null($shipping)) {
            return $this;
        }
#echo "NEW: "; var_dump($shipping);
        $vId = $vendor->getId();
        $table = $this->getTable('vendor_shipping');
        $write = $this->_getWriteAdapter();
        $rHlp = Mage::getResourceSingleton('udropship/helper');

        // retrieve old data for updated products
        $old = $vendor->getNonCachedShippingMethods();
#echo "OLD: "; var_dump($old);
        $insert = array();
        $delete = array();

        $newOld = array();
        foreach ($old as $__sId => $__old) {
            if (!is_array($__old)) continue;
            reset($__old);
            $__curKey = key($__old);
            $__cur = current($__old);
            if (is_numeric($__curKey)) {
                $newOld[$__sId] = $__cur;
            } else {
                $newOld[$__sId] = $__old;
            }
        }
        $old = $newOld;

        foreach ($shipping as $id=>$a) {
            if (!(int)$id) {
                continue;
            }
            // delete
            if (isset($a['on']) && $a['on']===false) {
                if (!empty($old[$id]['vendor_shipping_id'])) {
                    $delete[] = $old[$id]['vendor_shipping_id'];
                }
                continue;
            }
            // not needed any more
            unset($a['on']);
            // empty value means NULL
            foreach ($a as $k=>$v) {
                if ($v==='') {
                    $a[$k] = null;
                }
            }
            foreach (array('extra_charge_suffix','extra_charge_type','extra_charge') as $exField) {
                if (!empty($a[$exField.'_use_default']) && empty($a['allow_extra_charge'])) {
                    $a[$exField] = null;
                }
            }
            // insert
            if (!isset($old[$id])) {
                $a['vendor_id'] = $vId;
                $a['shipping_id'] = $id;
                $insert[] = $write->quoteInto('(?)',
                    $rHlp->myPrepareDataForTable($table, $a, true)
                );
            }
            // update
            elseif (!empty($old[$id]['vendor_shipping_id'])) {
                unset($a['vendor_shipping_id'], $a['vendor_id'], $a['shipping_id']); //security
                $a = $rHlp->myPrepareDataForTable($table, $a);
#echo 'UPDATE: '.$old[$id]['vendor_shipping_id'].': '; var_dump($a); exit;
                if (!empty($a)) {
                    $write->update($table, $a, 'vendor_shipping_id='.(int)$old[$id]['vendor_shipping_id']);
                }
            }
        }
        if ($insert) {
#echo "INSERT: "; var_dump($insert); exit;
            $write->query(sprintf("replace into %s (%s) values %s",
                $table,
                implode(',', array_keys($rHlp->myPrepareDataForTable($table, array(), true))),
                join(',', $insert)
            ));
        }
        if ($delete) {
#echo "DELETE: "; var_dump($delete);
            $write->delete($table, $write->quoteInto('vendor_shipping_id in (?)', $delete).$write->quoteInto(' AND vendor_id=?', $vId));
        }
#exit;
        $vendor->unsetData('shipping_methods');
        $vendor->unsetData('shipping_methods_system');
        $vendor->unsetData('posted_shipping');

        return $this;
    }

    public function updateData($object, $fields)
    {
        $condition = $this->_getWriteAdapter()->quoteInto($this->getIdFieldName().'=?', $object->getId());
        $data = array_intersect_key($this->_prepareDataForSave($object), $fields);
        $this->_getWriteAdapter()->update($this->getMainTable(), $data, $condition);
        return $this;
    }
}
