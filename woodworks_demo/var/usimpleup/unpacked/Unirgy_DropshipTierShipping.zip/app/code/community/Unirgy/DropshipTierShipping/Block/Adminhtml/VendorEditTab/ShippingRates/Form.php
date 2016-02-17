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

class Unirgy_DropshipTierShipping_Block_Adminhtml_VendorEditTab_ShippingRates_Form extends Mage_Adminhtml_Block_Widget_Form
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

        if (Mage::getStoreConfig('carriers/udtiership/use_simple_rates')) {

            $fieldset->addType('tiership_simple_rates', Mage::getConfig()->getBlockClassName('udtiership/adminhtml_vendorEditTab_shippingRates_form_simpleRates'));

            $fieldset->addField('tiership_simple_rates', 'tiership_simple_rates', array(
                'name'      => 'tiership_simple_rates',
                'label'     => Mage::helper('udropship')->__('Rates'),
            ));

        } else {

            $fieldset->addType('tiership_rates', Mage::getConfig()->getBlockClassName('udtiership/adminhtml_vendorEditTab_shippingRates_form_rates'));

            $fieldset->addField('tiership_rates', 'tiership_rates', array(
                'name'      => 'tiership_rates',
                'label'     => Mage::helper('udropship')->__('Rates'),
            ));

        }

        if ($vendor) {
            $form->setValues($vendor->getData());
        }

        return parent::_prepareForm();
    }

}