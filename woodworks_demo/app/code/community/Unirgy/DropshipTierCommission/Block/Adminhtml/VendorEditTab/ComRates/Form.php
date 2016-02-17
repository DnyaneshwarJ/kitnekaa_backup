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
 * @package    Unirgy_DropshipTierCommission
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

class Unirgy_DropshipTierCommission_Block_Adminhtml_VendorEditTab_ComRates_Form extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();
        $this->setDestElementId('vendor_tiercom');
    }

    protected function _prepareForm()
    {
        $vendor = Mage::registry('vendor_data');
        $hlp = Mage::helper('udropship');
        $id = $this->getRequest()->getParam('id');
        $form = new Varien_Data_Form();
        $this->setForm($form);

        $fieldset = $form->addFieldset('tiercom', array(
            'legend'=>Mage::helper('udropship')->__('Rates Definition')
        ));

        $fieldset->addField('tiercom_fallback_lookup', 'select', array(
            'name'      => 'tiercom_fallback_lookup',
            'label'     => Mage::helper('udropship')->__('Commission fallback lookup method'),
            'options'   => Mage::getSingleton('udtiercom/source')->setPath('tiercom_fallback_lookup')->toOptionHash(),
        ));

        $fieldset->addType('tiercom_rates', Mage::getConfig()->getBlockClassName('udtiercom/adminhtml_vendorEditTab_comRates_form_rates'));
        
        $fieldset->addField('tiercom_rates', 'tiercom_rates', array(
            'name'      => 'tiercom_rates',
            'label'     => Mage::helper('udropship')->__('Rates'),
        ));

        $fieldset->addField('tiercom_fixed_calc_type', 'select', array(
            'name'      => 'tiercom_fixed_calc_type',
            'label'     => Mage::helper('udropship')->__('Fixed Rates Calculation Type'),
            'options'   => Mage::getSingleton('udtiercom/source')->setPath('tiercom_fixed_calc_type')->toOptionHash(),
        ));

        $fieldset->addField('commission_percent', 'text', array(
            'name'      => 'commission_percent',
            'label'     => Mage::helper('udropship')->__('Default Commission Percent'),
            'after_element_html' => Mage::helper('udropship')->__('<br />Default value: %.2F. Leave empty to use default.', Mage::getStoreConfig('udropship/tiercom/commission_percent'))
        ));

        $fieldset->addField('transaction_fee', 'text', array(
            'name'      => 'transaction_fee',
            'label'     => Mage::helper('udropship')->__('Fixed Flat Rate (per po) [old transaction fee]'),
            'after_element_html' => Mage::helper('udropship')->__('<br />Default value: %.2F. Leave empty to use default.', Mage::getStoreConfig('udropship/tiercom/transaction_fee'))
        ));

        $fieldset->addType('tiercom_fixed_rule', Mage::getConfig()->getBlockClassName('udropship/adminhtml_vendor_helper_form_dependSelect'));

        $fieldset->addField('tiercom_fixed_rule', 'tiercom_fixed_rule', array(
            'name'      => 'tiercom_fixed_rule',
            'label'     => Mage::helper('udropship')->__('Rule for Fixed Rates'),
            'options'   => Mage::getSingleton('udtiercom/source')->setPath('tiercom_fixed_rates')->toOptionHash(),
            'field_config' => array(
                'hide_depend_fields' => array(
                    'tiercom_fixed_rates' => '',
                )
            )
        ));

        $fieldset->addType('tiercom_fixed_rates', Mage::getConfig()->getBlockClassName('udtiercom/adminhtml_vendorEditTab_comRates_form_fixedRates'));

        $fieldset->addField('tiercom_fixed_rates', 'tiercom_fixed_rates', array(
            'name'      => 'tiercom_fixed_rates',
            'label'     => Mage::helper('udropship')->__('Rule Based Fixed Rates'),
        ));

        if ($vendor) {
            $form->setValues($vendor->getData());
        }

        return parent::_prepareForm();
    }

}