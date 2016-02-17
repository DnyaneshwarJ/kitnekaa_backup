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
 * @package    Unirgy_DropshipMultiPrice
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

class Unirgy_DropshipMultiPrice_Model_Source extends Unirgy_Dropship_Model_Source_Abstract
{
    public function toOptionHash($selector=false)
    {
        $hlp = Mage::helper('udmultiprice');

        $options = array();

        switch ($this->getPath()) {

    	case 'vendor_product_state':
            $this->_initProductStates();
            $options = $this->_extendedStates;
            break;
        case 'vendor_product_state_canonic':
            $this->_initProductStates();
            $options = $this->_canonicStates;
            break;
            
        default:
            Mage::throwException(Mage::helper('udropship')->__('Invalid request for source options: '.$this->getPath()));
        }

        if ($selector) {
            $options = array(''=>Mage::helper('udropship')->__('* Please select')) + $options;
        }

        return $options;
    }

    protected $_canonicStatesByExt;
    protected $_canonicStates;
    protected $_defaultCanonicState;
    protected $_defaultExtState;
    protected $_extendedStates;
    protected $_extendedStatesByCan;

    public function getDefaultExtState()
    {
        $this->_initProductStates();
        return $this->_defaultExtState;
    }
    public function getDefaultCanonicState()
    {
        $this->_initProductStates();
        return $this->_defaultCanonicState;
    }

    protected function _initProductStates()
    {
        if (null === $this->_canonicStates) {
            $hlp = Mage::helper('udmultiprice');
            $stateXml = Mage::getConfig()->getNode('global/udropship/product_state');
            foreach ($stateXml->children() as $state) {
                $this->_canonicStates[$state->getName()] = Mage::helper('udropship')->__((string)$state->label);
                if ($state->extended && $state->extended->children()) {
                    foreach ($state->extended->children() as $extState) {
                        $this->_extendedStates[$extState->getName()] = Mage::helper('udropship')->__((string)$extState->label);
                        $this->_canonicStatesByExt[$extState->getName()] = $state->getName();
                        $this->_extendedStatesByCan[$state->getName()][] = $extState->getName();
                        if (isset($extState->is_default) && 'true' == (string)$extState->is_default
                            || !$this->_defaultExtState
                        ) {
                            $this->_defaultExtState     = $extState->getName();
                            $this->_defaultCanonicState = $state->getName();
                        }
                    }
                }
            }
        }
        return $this;
    }

    public function getCanonicState($canonicState, $returnType='code', $useDefault=false)
    {
        $this->_initProductStates();
        return $this->_getState($canonicState, 'canonic', $returnType, $useDefault);
    }

    public function getExtState($extState, $returnType='code', $useDefault=false)
    {
        $this->_initProductStates();
        return $this->_getState($extState, 'extended', $returnType, $useDefault);
    }

    protected function _getState($state, $type, $returnType='code', $useDefault=false)
    {
        $this->_initProductStates();
        $states = $type == 'canonic' ? $this->_canonicStates : $this->_extendedStates;
        if (!array_key_exists($state, $states)
            && false === array_search($state, $states)
        ) {
            if ($useDefault) {
                $state = ($type == 'canonic' ? $this->_defaultCanonicState : $this->_defaultExtState);
            } else {
                return null;
            }
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

    protected function _fallbackState($type, $useDefault)
    {
        $this->_initProductStates();
        return $useDefault
            ? ($type == 'canonic' ? $this->_defaultCanonicState : $this->_defaultExtState)
            : null;
    }

    public function getExtCanonicState($extendedState, $returnType='code', $useDefault=false)
    {
        $this->_initProductStates();
        if (!array_key_exists($extendedState, $this->_canonicStatesByExt)) {
            if ($useDefault) {
                $extendedState = $this->_defaultExtState;
            } else {
                return null;
            }
        }
        $canonic = $this->_canonicStatesByExt[$extendedState];
        switch ($returnType) {
            case 'pair':
                return array($canonic => $this->_canonicStates[$canonic]);
            case 'label':
                return $this->_canonicStates[$canonic];
            default:
                return $canonic;
        }
    }

    public function getCanonicExtStates($canonicState, $returnType='code', $useDefault=false)
    {
        $this->_initProductStates();
        if (!array_key_exists($canonicState, $this->_extendedStatesByCan)) {
            if ($useDefault) {
                $canonicState = $this->_defaultCanonicState;
            } else {
                return null;
            }
        }
        $extended = $this->_extendedStatesByCan[$canonicState];
        switch ($returnType) {
            case 'pair':
            case 'label':
                $return = array();
                foreach ($extended as $ext) {
                    $return[$ext] = $this->_extendedStates[$ext];
                }
                return $returnType == 'label' ? array_values($return) : $return;
            default:
                return $extended;
        }
    }

}
