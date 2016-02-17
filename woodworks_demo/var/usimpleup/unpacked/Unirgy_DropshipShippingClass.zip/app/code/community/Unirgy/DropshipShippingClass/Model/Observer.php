<?php

class Unirgy_DropshipShippingClass_Model_Observer
{
    public function udropship_adminhtml_vendor_edit_prepare_shipping_grid($observer)
    {
        $vendor = $observer->getVendor();
        $collection = $observer->getCollection();
        $collection->addFieldToFilter('vendor_ship_class', array(
            array('null'=>true),
            array('eq'=>''),
            array('finset'=>Mage::helper('udshipclass')->getVendorShipClass($vendor)),
        ));
    }
    public function udropship_adminhtml_shipping_grid_prepare_columns($observer)
    {
        $grid = $observer->getGrid();
        $grid->addColumn('customer_ship_class', array(
            'header'        => Mage::helper('udropship')->__('Customer Ship Class'),
            'index'         => 'customer_ship_class',
            'type'          => 'options',
            'options'       => Mage::getSingleton('udshipclass/source')->setPath('customer_ship_class')->toOptionHash(),
            'sortable'      => false,
            'filter'        => false,
        ));
        $grid->addColumn('vendor_ship_class', array(
            'header'        => Mage::helper('udropship')->__('Vendor Ship Class'),
            'index'         => 'vendor_ship_class',
            'type'          => 'options',
            'options'       => Mage::getSingleton('udshipclass/source')->setPath('vendor_ship_class')->toOptionHash(),
            'sortable'      => false,
            'filter'        => false,
        ));
    }
    public function udropship_adminhtml_shipping_grid_after_load($observer)
    {
        $grid = $observer->getGrid();
        foreach ($grid->getCollection() as $shipping) {
            Mage::helper('udshipclass')->processShipClass($shipping, 'vendor_ship_class');
            Mage::helper('udshipclass')->processShipClass($shipping, 'customer_ship_class');
        }
    }
    public function udropship_adminhtml_shipping_edit_prepare_form($observer)
    {
        $id = $observer->getEvent()->getId();
        $form = $observer->getEvent()->getForm();
        $fieldset = $form->getElement('shipping_form');

        $fieldset->addField('vendor_ship_class', 'multiselect', array(
            'name'      => 'vendor_ship_class',
            'label'     => Mage::helper('udropship')->__('Vendor Ship Class'),
            'values'   => Mage::getSingleton('udshipclass/source')->setPath('vendor_ship_class')->toOptionArray(true),
        ));
        $fieldset->addField('customer_ship_class', 'multiselect', array(
            'name'      => 'customer_ship_class',
            'label'     => Mage::helper('udropship')->__('Customer Ship Class'),
            'values'   => Mage::getSingleton('udshipclass/source')->setPath('customer_ship_class')->toOptionArray(true),
        ));
    }
    public function udropship_shipping_load_after($observer)
    {
        Mage::helper('udshipclass')->processShipClass($observer->getShipping(), 'vendor_ship_class');
        Mage::helper('udshipclass')->processShipClass($observer->getShipping(), 'customer_ship_class');
    }
    public function udropship_shipping_save_after($observer)
    {
        Mage::helper('udshipclass')->processShipClass($observer->getShipping(), 'vendor_ship_class');
        Mage::helper('udshipclass')->processShipClass($observer->getShipping(), 'customer_ship_class');
    }
    public function udropship_shipping_save_before($observer)
    {
        $r = Mage::app()->getRequest();
        if ($r->getParam('vendor_ship_class')) {
            $observer->getShipping()->setData('vendor_ship_class', $r->getParam('vendor_ship_class'));
        }
        if ($r->getParam('customer_ship_class')) {
            $observer->getShipping()->setData('customer_ship_class', $r->getParam('customer_ship_class'));
        }
        Mage::helper('udshipclass')->processShipClass($observer->getShipping(), 'vendor_ship_class', true);
        Mage::helper('udshipclass')->processShipClass($observer->getShipping(), 'customer_ship_class', true);
    }

    public function udropship_vendor_shipping_check_skipped($observer)
    {
        $shipping = $observer->getShipping();
        $vendor = $observer->getVendor();
        $address = $observer->getAddress();
        $result = $observer->getResult();
        $scHlp = Mage::helper('udshipclass');
        $scHlp->processShipClass($shipping, 'vendor_ship_class');
        $scHlp->processShipClass($shipping, 'customer_ship_class');
        $_vClass = $scHlp->getAllVendorShipClass($vendor);
        $_cClass = $scHlp->getAllCustomerShipClass($address);
        $vClass = $shipping->getVendorShipClass();
        $cClass = $shipping->getCustomerShipClass();
        $resFlag = null;
        if (!empty($vClass) && is_array($vClass) && !array_intersect($_vClass, $vClass)) {
            $resFlag = true;
        }
        if (!empty($cClass) && is_array($cClass) && !array_intersect($_cClass, $cClass)) {
            $resFlag = true;
        }
        if ($resFlag !== null) {
            $result->setResult($resFlag);
        }
    }

}