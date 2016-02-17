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

class Unirgy_Dropship_Model_Mysql4_Vendor_Backend extends Mage_Eav_Model_Entity_Attribute_Backend_Abstract
{
    protected static $_isEnabled;
    protected function _isEnabled()
    {
        if (is_null(self::$_isEnabled)) {
            $module = Mage::getConfig()->getNode('modules/Unirgy_Dropship');
            self::$_isEnabled = $module && $module->is('active');
        }
        return self::$_isEnabled;
    }

    public function getDefaultValue()
    {
        return parent::getDefaultValue();
        if (is_null($this->_defaultValue)) {
            $this->_defaultValue = Mage::helper('udropship')->getLocalVendorId($this->getAttribute()->getStoreId());
        }
        return $this->_defaultValue;
    }

    public function afterLoad($object)
    {
        parent::afterLoad($object);
        if (!$this->_isEnabled()) {
            return;
        }
        $attrCode = $this->getAttribute()->getAttributeCode();
        $defValue = $this->getDefaultValue();
        if (!$object->getData($attrCode) && $defValue) {
            $object->setData($attrCode, $defValue);
        }
    }
}