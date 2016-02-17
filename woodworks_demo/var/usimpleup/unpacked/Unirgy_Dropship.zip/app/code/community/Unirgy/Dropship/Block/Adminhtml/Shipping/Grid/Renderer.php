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

class Unirgy_Dropship_Block_Adminhtml_Shipping_Grid_Renderer
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $index = $this->getColumn()->getIndex();
        $value = $row->getData($index);
        switch ($index) {
        case 'system_methods_by_profile':
            return $this->_renderMethods($value);
        case 'website_ids':
            return $this->_renderWebsites($value);
        }
    }

    protected function _renderMethods($systemMethods)
    {
        if (!$systemMethods) {
            return '';
        }
        $carriers = Mage::getSingleton('shipping/config')->getAllCarriers();
        foreach ($carriers as $carrierCode=>$carrierModel) {
            /*
            if (!$carrierModel->isActive() && (bool)$isActiveOnlyFlag === true) {
                continue;
            }
            */
            if ($carrierCode=='ups') {
                $methodsNested = Mage::getSingleton('udropship/source')->setPath('ups_shipping_method_combined')->toOptionHash();
                foreach ($methodsNested as $api=>$ms) {
                    foreach ($ms as $k=>$v) {
                        $carrierMethods[$k] = "($api) $v";
                    }
                }
            } else {
                $carrierMethods = $carrierModel->getAllowedMethods();
                if (!$carrierMethods) {
                    $carrierMethods = array();
                }
            }
            $carrierTitle = Mage::getStoreConfig('carriers/'.$carrierCode.'/title');
            foreach ($carrierMethods as $methodCode=>$methodTitle) {
                $methods[$carrierCode][$methodCode] = $carrierTitle.' - '.$methodTitle;
            }
            $methods[$carrierCode]['*'] = $carrierTitle.' - Any available';
        }

        $result = array();
        foreach ($systemMethods as $p=>$__m) {
            foreach ($__m as $c=>$_m) {
                foreach ($_m as $m) {
                    if (isset($methods[$c][$m])) {
                        $result[] = $methods[$c][$m];
                    } else {
                        $result[] = $c.' - '.$m.' (not found)';
                    }
                }
            }
        }

        return $result ? join('<br/> ', $result) : '&nbsp;';

    }

    protected function _renderWebsites($websites)
    {
        $result = array();
        if ($websites == array(0)) {
            return Mage::helper('udropship')->__('All websites');
        }
        foreach ($websites as $id) {
            $result[] = Mage::app()->getWebsite($id)->getName();
        }
        return $result ? join('<br/> ', $result) : '&nbsp;';
    }

}
