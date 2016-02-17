<?php

class Unirgy_DropshipTierCommission_Model_Observer
{
    public function udropship_adminhtml_vendor_tabs_after($observer)
    {
        $block = $observer->getBlock();
        $block->addTab('udtiercom', array(
            'label'     => Mage::helper('udropship')->__('Tier Commissions'),
            'after'     => 'shipping_section',
            'content'   => Mage::app()->getLayout()->createBlock('udtiercom/adminhtml_vendorEditTab_comRates_form', 'vendor.tiercom.form')
                ->toHtml()
        ));
    }
    public function udropship_vendor_load_after($observer)
    {
        Mage::helper('udtiercom')->processTiercomRates($observer->getVendor());
        Mage::helper('udtiercom')->processTiercomFixedRates($observer->getVendor());
    }
    public function udropship_vendor_save_after($observer)
    {
        Mage::helper('udtiercom')->processTiercomRates($observer->getVendor());
        Mage::helper('udtiercom')->processTiercomFixedRates($observer->getVendor());
    }
    public function udropship_vendor_save_before($observer)
    {
        Mage::helper('udtiercom')->processTiercomRates($observer->getVendor(), true);
        Mage::helper('udtiercom')->processTiercomFixedRates($observer->getVendor(), true);
    }

    public function udpo_order_save_before($observer)
    {
        $order = $observer->getOrder();
        $pos = $observer->getUdpos();

        foreach ($pos as $po) {
            Mage::helper('udtiercom')->processPo($po);
        }
    }
    public function udpo_po_shipment_save_before($observer)
    {
        $order = $observer->getOrder();
        $pos = $observer->getShipments();

        foreach ($pos as $po) {
            Mage::helper('udtiercom')->processPo($po);
        }
    }

}