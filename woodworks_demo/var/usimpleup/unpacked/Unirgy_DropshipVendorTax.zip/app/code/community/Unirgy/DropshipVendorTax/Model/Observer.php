<?php

class Unirgy_DropshipVendorTax_Model_Observer
{
    public function udropship_adminhtml_vendor_grid_prepare_columns($observer)
    {
        $grid = $observer->getGrid();
        $grid->addColumn('vendor_tax_class', array(
            'header'        => Mage::helper('udropship')->__('Vendor Tax Class'),
            'index'         => 'vendor_tax_class',
            'type'          => 'options',
            'options'       => Mage::getSingleton('udtax/source')->setPath('vendor_tax_class')->toOptionHash(),
        ));
        $grid->addColumnsOrder('action', 'vendor_tax_class');
    }
    public function udropship_adminhtml_vendor_grid_prepare_massaction($observer)
    {
        $grid = $observer->getGrid();
        $grid->getMassactionBlock()->addItem('vendor_tax_class', array(
            'label'=> Mage::helper('udropship')->__('Change Vendor Tax Class'),
            'url'  => $grid->getUrl('adminhtml/udtaxadmin_index/massUpdateVendorTaxClass'),
            'additional' => array(
                'vendor_tax_class' => array(
                    'name' => 'vendor_tax_class',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => Mage::helper('udropship')->__('Vendor Tax Class'),
                    'values' => Mage::getSingleton('udtax/source')->setPath('vendor_tax_class')->toOptionHash(true),
                )
            )
        ));
    }
    public function udropship_adminhtml_vendor_edit_prepare_form($observer)
    {
        $form = $observer->getForm();
        $vForm = $form->getElement('vendor_form');
        if ($vForm) {
            $hlp = Mage::helper('udropship');
            $vForm->addField('vendor_tax_class', 'select', array(
                'name'      => 'vendor_tax_class',
                'label'     => Mage::helper('udropship')->__('Vendor Tax Class'),
                'values'    => Mage::getSingleton('udtax/source')->setPath('vendor_tax_class')->toOptionArray(),
            ));
        }
    }
    public function udropship_vendor_front_preferences($observer)
    {
        $data = $observer->getEvent()->getData();
    }
    public function udropship_vendor_preferences_save_before($observer)
    {
        $data = $observer->getEvent()->getData();
        $v = $data['vendor'];
        $p = $data['post_data'];
        foreach (array(
                 ) as $f) {
            $v->setData($f, @$p[$f]);
        }
    }
    public function udropship_vendor_save_commit_after($observer)
    {
        $vendor = $observer->getVendor();
        Mage::helper('udtax')->processVendorChange($vendor);
    }

    public function udropship_quote_item_setUdropshipVendor($observer)
    {
        $observer->getItem()->setVendorTaxClass(
            Mage::helper('udropship')->getVendor($observer->getItem()->getUdropshipVendor())->getVendorTaxClass()
        );
    }

    public function controller_front_init_before($observer)
    {
        $this->_initConfigRewrites();
    }
    public function udropship_init_config_rewrites()
    {
        $this->_initConfigRewrites();
    }
    protected function _initConfigRewrites()
    {
        Mage::getConfig()->setNode('global/helpers/tax/rewrite/data', 'Unirgy_DropshipVendorTax_Helper_Tax');
        if (
        Mage::helper('udropship')->compareMageVer('1.7.0.0', '1.12.0.0')
        ) {
            Mage::getConfig()->setNode('global/blocks/adminhtml/rewrite/tax_class', 'Unirgy_DropshipVendorTax_Block_Adminhtml_Rewrite1700_Tax_Class');
            Mage::getConfig()->setNode('global/blocks/adminhtml/rewrite/tax_class_edit_form', 'Unirgy_DropshipVendorTax_Block_Adminhtml_Rewrite1700_Tax_Class_Edit_Form');
            Mage::getConfig()->setNode('global/blocks/adminhtml/rewrite/tax_rule_edit_form', 'Unirgy_DropshipVendorTax_Block_Adminhtml_Rewrite1700_Tax_Rule_Edit_Form');

            if (
            Mage::helper('udropship')->compareMageVer('1.8.1.0', '1.13.0.0')
            ) {
                Mage::getConfig()->setNode('global/helpers/tax/rewrite/data', 'Unirgy_DropshipVendorTax_Helper_Tax19');
                Mage::getConfig()->setNode('global/models/tax/rewrite/calculation', 'Unirgy_DropshipVendorTax_Model_Rewrite1900_Tax_Calculation');
                Mage::getConfig()->setNode('global/models/tax/rewrite/calculation_rule', 'Unirgy_DropshipVendorTax_Model_Rewrite1900_Tax_Calculation_Rule');
                Mage::getConfig()->setNode('global/models/tax_resource/rewrite/calculation_rule', 'Unirgy_DropshipVendorTax_Model_Rewrite1900_Tax_Resource_Calculation_Rule');
                Mage::getConfig()->setNode('global/models/tax/rewrite/sales_total_quote_subtotal', 'Unirgy_DropshipVendorTax_Model_Rewrite1900_Tax_Sales_Total_Quote_Subtotal');
                Mage::getConfig()->setNode('global/models/tax/rewrite/sales_total_quote_tax', 'Unirgy_DropshipVendorTax_Model_Rewrite1900_Tax_Sales_Total_Quote_Tax');
                Mage::getConfig()->setNode('global/models/tax/rewrite/sales_total_quote_shipping', 'Unirgy_DropshipVendorTax_Model_Rewrite1900_Tax_Sales_Total_Quote_Shipping');
                Mage::getConfig()->setNode('global/models/tax_resource/rewrite/calculation', 'Unirgy_DropshipVendorTax_Model_Rewrite1900_Tax_Resource_Calculation');
            } else {
                Mage::getConfig()->setNode('global/models/tax/rewrite/calculation', 'Unirgy_DropshipVendorTax_Model_Rewrite1700_Tax_Calculation');
                Mage::getConfig()->setNode('global/models/tax/rewrite/calculation_rule', 'Unirgy_DropshipVendorTax_Model_Rewrite1700_Tax_Calculation_Rule');
                Mage::getConfig()->setNode('global/models/tax/rewrite/sales_total_quote_subtotal', 'Unirgy_DropshipVendorTax_Model_Rewrite1700_Tax_Sales_Total_Quote_Subtotal');
                Mage::getConfig()->setNode('global/models/tax/rewrite/sales_total_quote_tax', 'Unirgy_DropshipVendorTax_Model_Rewrite1700_Tax_Sales_Total_Quote_Tax');
                Mage::getConfig()->setNode('global/models/tax/rewrite/sales_total_quote_shipping', 'Unirgy_DropshipVendorTax_Model_Rewrite1700_Tax_Sales_Total_Quote_Shipping');
                Mage::getConfig()->setNode('global/models/tax_resource/rewrite/calculation', 'Unirgy_DropshipVendorTax_Model_Rewrite1700_Tax_Resource_Calculation');
            }

            Mage::getConfig()->setNode('global/models/tax_resource/rewrite/calculation_rule_collection', 'Unirgy_DropshipVendorTax_Model_Rewrite1700_Tax_Resource_Calculation_Rule_Collection');
        }
        elseif (
        Mage::helper('udropship')->compareMageVer('1.6.0.0', '1.11.0.0')
        ) {
            Mage::getConfig()->setNode('global/blocks/adminhtml/rewrite/tax_class', 'Unirgy_DropshipVendorTax_Block_Adminhtml_Rewrite1600_Tax_Class');
            Mage::getConfig()->setNode('global/blocks/adminhtml/rewrite/tax_class_edit_form', 'Unirgy_DropshipVendorTax_Block_Adminhtml_Rewrite1600_Tax_Class_Edit_Form');
            Mage::getConfig()->setNode('global/blocks/adminhtml/rewrite/tax_rule_edit_form', 'Unirgy_DropshipVendorTax_Block_Adminhtml_Rewrite1600_Tax_Rule_Edit_Form');

            Mage::getConfig()->setNode('global/models/tax/rewrite/calculation', 'Unirgy_DropshipVendorTax_Model_Rewrite1600_Tax_Calculation');
            Mage::getConfig()->setNode('global/models/tax/rewrite/calculation_rule', 'Unirgy_DropshipVendorTax_Model_Rewrite1600_Tax_Calculation_Rule');
            Mage::getConfig()->setNode('global/models/tax/rewrite/sales_total_quote_subtotal', 'Unirgy_DropshipVendorTax_Model_Rewrite1600_Tax_Sales_Total_Quote_Subtotal');
            Mage::getConfig()->setNode('global/models/tax/rewrite/sales_total_quote_tax', 'Unirgy_DropshipVendorTax_Model_Rewrite1600_Tax_Sales_Total_Quote_Tax');

            Mage::getConfig()->setNode('global/models/tax_resource/rewrite/calculation', 'Unirgy_DropshipVendorTax_Model_Rewrite1600_Tax_Resource_Calculation');
        }
        if (!Mage::helper('udropship')->isUdsplitActive()) {
            Mage::getConfig()->setNode('global/blocks/checkout/rewrite/cart_shipping', 'Unirgy_DropshipVendorTax_Block_CartShipping');
            Mage::getConfig()->setNode('global/blocks/checkout/rewrite/onepage_shipping_method_available', 'Unirgy_DropshipVendorTax_Block_OnepageShipping');
        }
    }

}