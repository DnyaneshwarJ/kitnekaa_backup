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
 * @package    Unirgy_DropshipSplit
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

/**
* Currently not in use
*/
class Unirgy_DropshipMulti_Model_Source extends Unirgy_Dropship_Model_Source_Abstract
{
    const AVAIL_BACKORDERS_YES_NONOTIFY=10;
    const AVAIL_BACKORDERS_YES_NOTIFY=11;
    const BACKORDERS_USE_CONFIG = -1;
    public function toOptionHash($selector=false)
    {
        $hlp = Mage::helper('udropship');
        $hlpm = Mage::helper('udmulti');

        switch ($this->getPath()) {

        case 'udropship/stock/total_qty_method':
            $options = array(
                'max' => 'Max available from any associated vendor',
                'sum' => 'Sum stock of all associated vendors',
            );
            break;

        case 'udropship/stock/default_multivendor_status':
        case 'vendor_product_status':
            $options = array(
                -1 => Mage::helper('udropship')->__('Pending'),
                0 => Mage::helper('udropship')->__('Inactive'),
                1 => Mage::helper('udropship')->__('Active'),
            );
            break;

        case 'avail_state':
            $this->_initAvailabilityStates();
            $options = $this->_availabilityState;
                
        case 'backorders':
            $options = array(
                self::BACKORDERS_USE_CONFIG => Mage::helper('udropship')->__('* Use Config'),
            );
            foreach (Mage::getSingleton('cataloginventory/source_backorders')->toOptionArray() as $opt) {
                $options[$opt['value']] = $opt['label'];
            }
            break;

        default:
            Mage::throwException(Mage::helper('udropship')->__('Invalid request for source options: '.$this->getPath()));
        }

        if ($selector) {
            $options = array(''=>Mage::helper('udropship')->__('* Please select')) + $options;
        }

        return $options;
    }

    protected $_availabilityState;
    protected function _initAvailabilityStates()
    {
        if (null === $this->_canonicStates) {
            $hlp = Mage::helper('udmulti');
            $stateXml = Mage::getConfig()->getNode('global/udropship/avail_state');
            foreach ($stateXml->children() as $state) {
                $this->_availabilityState[$state->getName()] = Mage::helper('udropship')->__((string)$state->label);
            }
        }
        return $this;
    }
    public function getAvailState($state, $returnType='code')
    {
        return $this->getAvailabilityState($state, $returnType);
    }
    public function getAvailabilityState($state, $returnType='code')
    {
        $this->_initAvailabilityStates();
        $states = $this->_availabilityState;
        if (!array_key_exists($state, $states)
            && false === array_search($state, $states)
        ) {
            return null;
        }
        switch ($returnType) {
            case 'pair':
                return array_key_exists($state, $states)
                    ? array($state => $states[$state])
                    : array(array_search($state, $states) => $state);
            case 'label':
                return $states[$state];
            default:
                return array_key_exists($state, $states)
                    ? $state : array_search($state, $states);
        }
    }
}