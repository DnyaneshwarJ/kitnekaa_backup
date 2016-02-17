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
 * @package    Unirgy_DropshipTierShipping
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

class Unirgy_DropshipTierShipping_Block_Adminhtml_VendorEditTab_ShippingRates_V2_Form extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();
        $this->setDestElementId('vendor_tiership');
    }

    protected function _prepareForm()
    {
        $vendor = Mage::registry('vendor_data');
        $hlp = Mage::helper('udropship');
        $id = $this->getRequest()->getParam('id');
        $form = new Varien_Data_Form();
        $this->setForm($form);

        $fieldset = $form->addFieldset('tiership', array(
            'legend'=>Mage::helper('udropship')->__('Rates Definition')
        ));

        $fieldset->addType('tiership_use_v2_rates', Mage::getConfig()->getBlockClassName('udropship/adminhtml_vendor_helper_form_dependSelect'));

        $fieldset->addField('tiership_use_v2_rates', 'tiership_use_v2_rates', array(
            'name'      => 'tiership_use_v2_rates',
            'label'     => Mage::helper('udropship')->__('Use Vendor Specific Rates'),
            'options'   => Mage::getSingleton('udropship/source')->setPath('yesno')->toOptionHash(),
            'field_config' => array(
                'depend_fields' => array(
                    'tiership_delivery_type_selector' => '1',
                    'tiership_v2_rates' => '1',
                    'tiership_v2_simple_rates' => '1',
                    'tiership_v2_simple_cond_rates' => '1',
                )
            )
        ));

        $fieldset->addType('tiership_delivery_type_selector', Mage::getConfig()->getBlockClassName('udtiership/adminhtml_vendorEditTab_shippingRates_v2_form_deliveryTypeSelector'));

        $fieldset->addField('tiership_delivery_type_selector', 'tiership_delivery_type_selector', array(
            'name'      => 'tiership_delivery_type_selector',
            'label'     => Mage::helper('udropship')->__('Select Delivery Type To Setup Rates'),
            'options'   => Mage::getSingleton('udtiership/source')->setPath('tiership_delivery_type_selector')->toOptionHash(),
        ));

        if (Mage::helper('udtiership')->isV2SimpleRates()) {

            $fieldset->addType('tiership_v2_simple_rates', Mage::getConfig()->getBlockClassName('udtiership/adminhtml_vendorEditTab_shippingRates_v2_form_simpleRates'));

            $fieldset->addField('tiership_v2_simple_rates', 'tiership_v2_simple_rates', array(
                'name'      => 'tiership_v2_simple_rates',
                'label'     => Mage::helper('udropship')->__('Rates'),
            ));

        } elseif (Mage::helper('udtiership')->isV2SimpleConditionalRates()) {

            $fieldset->addType('tiership_v2_simple_cond_rates', Mage::getConfig()->getBlockClassName('udtiership/adminhtml_vendorEditTab_shippingRates_v2_form_simpleCondRates'));

            $fieldset->addField('tiership_v2_simple_cond_rates', 'tiership_v2_simple_cond_rates', array(
                'name'      => 'tiership_v2_simple_cond_rates',
                'label'     => Mage::helper('udropship')->__('Rates'),
            ));

        } else {

            $fieldset->addType('tiership_v2_rates', Mage::getConfig()->getBlockClassName('udtiership/adminhtml_vendorEditTab_shippingRates_v2_form_rates'));

            $fieldset->addField('tiership_v2_rates', 'tiership_v2_rates', array(
                'name'      => 'tiership_v2_rates',
                'label'     => Mage::helper('udropship')->__('Rates'),
            ));

        }

        if ($vendor) {
            $form->setValues($vendor->getData());
        }

        return parent::_prepareForm();
    }

}