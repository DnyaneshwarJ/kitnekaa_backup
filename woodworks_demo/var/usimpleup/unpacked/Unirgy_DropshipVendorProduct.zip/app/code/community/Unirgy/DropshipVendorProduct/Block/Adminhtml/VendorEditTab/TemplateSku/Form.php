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
 * @package    Unirgy_DropshipVendorProduct
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

class Unirgy_DropshipVendorProduct_Block_Adminhtml_VendorEditTab_TemplateSku_Form extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();
        $this->setDestElementId('vendor_udprod_template_sku');
    }

    protected function _prepareForm()
    {
        $vendor = Mage::registry('vendor_data');
        $hlp = Mage::helper('udropship');
        $id = $this->getRequest()->getParam('id');
        $form = new Varien_Data_Form();
        $this->setForm($form);

        $fieldset = $form->addFieldset('udprod_template_sku.form', array(
            'legend'=>Mage::helper('udropship')->__('Template SKUs')
        ));

        $fieldset->addType('udprod_template_sku', Mage::getConfig()->getBlockClassName('udprod/adminhtml_vendorEditTab_templateSku_form_templateSku'));

        $fieldset->addField('udprod_allow_choose_configurable', 'select', array(
            'name'      => 'udprod_allow_choose_configurable',
            'label'     => Mage::helper('udropship')->__('Allow vendor choose configurable attributes'),
            'options'   => Mage::getSingleton('udropship/source')->setPath('yesno')->toOptionHash()
        ));

        $fieldset->addField('udprod_template_sku', 'udprod_template_sku', array(
            'name'      => 'udprod_template_sku',
            'label'     => Mage::helper('udropship')->__('Product SKUs used as templates (per attribute set)'),
        ));

        if ($vendor) {
            $form->setValues($vendor->getData());
        }

        return parent::_prepareForm();
    }

}
