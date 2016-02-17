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

class Unirgy_Dropship_Block_Adminhtml_Shipping_Edit_Tab_Methods extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $shipping = Mage::registry('shipping_data');

        if ($shipping) {
            $systemMethods = $shipping->getSystemMethods();
        }

        $form = new Varien_Data_Form();
        $fieldset = $form->addFieldset('methods_fieldset', array('legend'=>Mage::helper('udropship')->__('Associated System Methods')));

        $carriers = Mage::getSingleton('shipping/config')->getAllCarriers();
        foreach ($carriers as $carrierCode=>$carrierModel) {
            if (in_array($carrierCode, array('udsplit', 'udropship','googlecheckout'))) {
                continue;
            }
            /*
            if (!$carrierModel->isActive() && (bool)$isActiveOnlyFlag === true) {
                continue;
            }
            */

            $params = array(
                'label'=>Mage::getStoreConfig('carriers/'.$carrierCode.'/title'),
                'name'=>'system_methods['.$carrierCode.']',
                'type'=>'options',
                'value'=>isset($systemMethods[$carrierCode]) ? $systemMethods[$carrierCode] : '',
            );

            if ($carrierCode=='ups') {
                $params['values'] = array_merge_recursive(
                    array(array('value'=>'', 'label'=>Mage::helper('udropship')->__('* Not used'))),
                    array(array('value'=>'*', 'label'=>Mage::helper('udropship')->__('* Any available'))),
                    Mage::getSingleton('udropship/source')->setPath('ups_shipping_method_combined')->toOptionArray()
                );
            } else {
                $carrierMethods = $carrierModel->getAllowedMethods();
                if (!$carrierMethods) {
                    $params['options'] = Mage::helper('udropship')->array_merge_n(
                        array(''=>Mage::helper('udropship')->__('* Not used')),
                        array('*'=>Mage::helper('udropship')->__('* Any available'))
                    );
                } else {
                    $params['options'] = Mage::helper('udropship')->array_merge_n(
                        array(''=>Mage::helper('udropship')->__('* Not used')),
                        array('*'=>Mage::helper('udropship')->__('* Any available')),
                        $carrierMethods
                    );
                }
            }

            $fieldset->addField('system_methods_'.$carrierCode, 'select', $params);
        }

/*
        $form->getElement('carriers_table')->setRenderer(
            $this->getLayout()->createBlock('udropship/adminhtml_vendor_edit_tab_carriers_grid')
        );
*/
        $this->setForm($form);
    }
}// Class Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Price END
