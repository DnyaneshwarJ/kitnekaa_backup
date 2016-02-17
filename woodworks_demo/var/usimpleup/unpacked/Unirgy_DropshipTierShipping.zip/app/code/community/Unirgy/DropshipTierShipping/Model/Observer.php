<?php

class Unirgy_DropshipTierShipping_Model_Observer
{
    public function udprod_product_edit_element_types($observer)
    {
        $response = $observer->getResponse();
        $types = $response->getTypes();
        $types['udtiership_rates'] = Mage::getConfig()->getBlockClassName('udtiership/vendor_product_form_rates');
        $types['text_udtiership_rates'] = Mage::getConfig()->getBlockClassName('udtiership/vendor_product_form_rates');
        $response->setTypes($types);
    }
    public function udropship_adminhtml_vendor_tabs_after($observer)
    {
        $tsHlp = Mage::helper('udtiership');
        $block = $observer->getBlock();
        if (!$tsHlp->isV2Rates()) {
            $block->addTab('udtiership', array(
                'label'     => Mage::helper('udropship')->__('Shipping Rates'),
                'after'     => 'shipping_section',
                'content'   => Mage::app()->getLayout()->createBlock('udtiership/adminhtml_vendorEditTab_shippingRates_form', 'vendor.tiership.form')
                    ->toHtml()
            ));
        } else {
            $block->addTab('udtiership', array(
                'label'     => Mage::helper('udropship')->__('Shipping Rates'),
                'after'     => 'shipping_section',
                'content'   => Mage::app()->getLayout()->createBlock('udtiership/adminhtml_vendorEditTab_shippingRates_v2_form', 'vendor.tiership.form')
                    ->toHtml()
            ));
        }
    }
    public function udropship_vendor_load_after($observer)
    {
        Mage::helper('udtiership')->processTiershipRates($observer->getVendor());
        Mage::helper('udtiership')->processTiershipSimpleRates($observer->getVendor());
    }
    public function udropship_vendor_save_after($observer)
    {
        $v = $observer->getVendor();
        Mage::helper('udtiership')->processTiershipRates($v);
        Mage::helper('udtiership')->processTiershipSimpleRates($v);
        Mage::helper('udtiership')->saveVendorV2Rates($v->getId(), $v->getData('tiership_v2_rates'));
        Mage::helper('udtiership')->saveVendorV2SimpleRates($v->getId(), $v->getData('tiership_v2_simple_rates'));
        Mage::helper('udtiership')->saveVendorV2SimpleCondRates($v->getId(), $v->getData('tiership_v2_simple_cond_rates'));
    }
    public function udropship_vendor_save_before($observer)
    {
        Mage::helper('udtiership')->processTiershipRates($observer->getVendor(), true);
        Mage::helper('udtiership')->processTiershipSimpleRates($observer->getVendor(), true);
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
        if (!Mage::helper('udtiership')->isV2Rates()) return;
        Mage::getConfig()->setNode('global/models/udtiership/rewrite/carrier', 'Unirgy_DropshipTierShipping_Model_V2_Carrier');
        foreach (array(
                     Mage::app()->getStore(),
                     Mage::app()->getStore(0),
                 ) as $store) {
            $store->setConfig('carriers/udtiership/udtiership/model', 'udtiership/v2_carrier');
            Mage::getConfig()->setNode('default/carriers/udtiership/model', 'udtiership/v2_carrier');
        }
    }

}