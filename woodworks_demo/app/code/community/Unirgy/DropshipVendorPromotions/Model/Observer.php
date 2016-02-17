<?php

class Unirgy_DropshipVendorPromotions_Model_Observer
{
    public function adminhtml_promo_quote_edit_tab_main_prepare_form($observer)
    {
        $options = array(''=>'')+Mage::getSingleton('udropship/source')->setPath('vendors')->toOptionHash();
        $value = null;
        if (Mage::registry('current_promo_quote_rule')) {
            $value = Mage::registry('current_promo_quote_rule')->getUdropshipVendor();
        }
        $observer->getForm()->getElement('base_fieldset')
            ->addField('udropship_vendor', 'select', array(
                'label'     => Mage::helper('udropship')->__('Dropship Vendor'),
                'title'     => Mage::helper('udropship')->__('Dropship Vendor'),
                'name'      => 'udropship_vendor',
                'options' => $options,
                'value' => $value
        ));
    }

}