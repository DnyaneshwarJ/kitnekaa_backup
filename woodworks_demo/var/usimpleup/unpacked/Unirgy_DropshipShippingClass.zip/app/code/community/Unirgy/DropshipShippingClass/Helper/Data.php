<?php

class Unirgy_DropshipShippingClass_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getCustomerShipClass($address=null)
    {
        return $this->_getCustomerShipClass($address, false);
    }
    public function getAllCustomerShipClass($address=null)
    {
        return $this->_getCustomerShipClass($address, true);
    }
    protected function _getCustomerShipClass($address=null, $all=false)
    {
        $shipClass = $all ? array() : -1;
        $cSess = Mage::getSingleton('customer/session');
        if (null == $address && $cSess->isLoggedIn()) {
            $address = $cSess->getCustomer()->getDefaultShippingAddress();
        }
        if ($address) {
            foreach ($this->getSortedCustomerShipClasses() as $cShipClass) {
                if (!$cShipClass->getRows()) {
                    if ($all) {
                        $shipClass[] = $cShipClass->getId();
                    } else {
                        $shipClass = $cShipClass->getId();
                        break;
                    }
                }
                foreach ($cShipClass->getRows() as $row) {
                    if ($address->getCountryId()==$row['country_id']
                        && $this->_checkRegion($address, $row)
                        && Mage::helper('udropship')->isZipcodeMatch($address->getPostcode(), $row['postcode'])
                    ) {
                        if ($all) {
                            $shipClass[] = $cShipClass->getId();
                        } else {
                            $shipClass = $cShipClass->getId();
                            break 2;
                        }
                    }
                }
            }
        }
        if ($all) {
            $shipClass[] = -1;
            $shipClass[] = '*';
        }
        return $shipClass;
    }
    public function getVendorShipClass($vendor=null)
    {
        return $this->_getVendorShipClass($vendor, false);
    }
    public function getAllVendorShipClass($vendor=null)
    {
        return $this->_getVendorShipClass($vendor, true);
    }
    protected function _getVendorShipClass($vendor, $all=false)
    {
        $shipClass = $all ? array() : -1;
        $vendor = Mage::helper('udropship')->getVendor($vendor);
        foreach ($this->getSortedVendorShipClasses() as $vShipClass) {
            if (!$vShipClass->getRows()) {
                if ($all) {
                    $shipClass[] = $vShipClass->getId();
                } else {
                    $shipClass = $vShipClass->getId();
                    break;
                }
            }
            foreach ($vShipClass->getRows() as $row) {
                if ($vendor->getCountryId()==$row['country_id']
                    && $this->_checkRegion($vendor, $row)
                    && Mage::helper('udropship')->isZipcodeMatch($vendor->getZip(), $row['postcode'])
                ) {
                    if ($all) {
                        $shipClass[] = $vShipClass->getId();
                    } else {
                        $shipClass = $vShipClass->getId();
                        break 2;
                    }
                }
            }
        }
        if ($all) {
            $shipClass[] = -1;
            $shipClass[] = '*';
        }
        return $shipClass;
    }
    protected function _checkRegion($obj1, $row)
    {
        $regionIds = explode(',', $row['region_id']);
        $regionIds = array_filter($regionIds);
        if (empty($regionIds)) return true;
        $rFilterKey = Mage::helper('udropship')->hasMageFeature('resource_1.6')
            ? 'main_table.region_id' : 'region.region_id';
        $regions = Mage::getResourceModel('directory/region_collection')
            ->addCountryFilter($row['country_id'])
            ->addFieldToFilter($rFilterKey, array('in'=>$regionIds));
        if ($regions->count()==0 || $regions->getItemById($obj1->getRegionId())) return true;
        return false;
    }
    public function processShipClass($shipping, $field, $serialize=false)
    {
        $shipClass = $shipping->getData($field);
        if ($serialize) {
            if (is_array($shipClass)) {
                $shipClass = array_filter($shipClass);
                $shipClass = implode(',', $shipClass);
            }
        } else {
            if (is_string($shipClass)) {
                $shipClass = explode(',', $shipClass);
            }
            if (!is_array($shipClass)) {
                $shipClass = array();
            }
            $shipClass = array_filter($shipClass);
        }
        $shipping->setData($field, $shipClass);
    }

    protected $_sortedCustomerShipClasses;
    public function getSortedCustomerShipClasses()
    {
        if (null == $this->_sortedCustomerShipClasses) {
            $this->_sortedCustomerShipClasses = Mage::getResourceModel('udshipclass/customer_collection')->addSortOrder();
        }
        return $this->_sortedCustomerShipClasses;
    }

    protected $_sortedVendorShipClasses;
    public function getSortedVendorShipClasses()
    {
        if (null == $this->_sortedVendorShipClasses) {
            $this->_sortedVendorShipClasses = Mage::getResourceModel('udshipclass/vendor_collection')->addSortOrder();
        }
        return $this->_sortedVendorShipClasses;
    }
}
