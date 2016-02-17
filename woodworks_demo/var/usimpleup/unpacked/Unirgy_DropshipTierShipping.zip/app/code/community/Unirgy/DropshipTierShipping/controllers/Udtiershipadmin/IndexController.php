<?php

class Unirgy_DropshipTierShipping_Udtiershipadmin_IndexController extends Mage_Adminhtml_Controller_Action
{
    public function loadRatesAction()
    {
        $tsHlp = Mage::helper('udtiership');
        $deliveryType = $this->getRequest()->getParam('delivery_type');
        $ctCost = $this->getRequest()->getParam('ct_cost');
        $ctAdditional = $this->getRequest()->getParam('ct_additional');
        $ctHandling = $this->getRequest()->getParam('ct_handling');
        $handlingApply = $this->getRequest()->getParam('handling_apply');
        $calculationMethod = $this->getRequest()->getParam('calculation_method');
        $useSimple = $this->getRequest()->getParam('use_simple');
        if (!Mage::helper('udtiership')->isV2Rates($useSimple) || !$deliveryType) {
            Mage::app()->getResponse()->setBody('');
            return ;
        }
        $_form = new Varien_Data_Form();
        if (Mage::helper('udtiership')->isV2SimpleRates($useSimple)) {
            $tplSkuEl = $_form->addField('carriers_udtiership_v2_simple_rates', 'select', array(
                'name'=>'groups[udtiership][fields][v2_simple_rates][value]',
                'label'=>Mage::helper('udropship')->__('V2 Simple First/Additional Rates'),
                'value'=>$tsHlp->getV2SimpleRates($deliveryType)
            ));
            $renderer = Mage::app()->getLayout()->createBlock('udtiership/adminhtml_systemConfigField_v2_simpleRates');
        } elseif (Mage::helper('udtiership')->isV2SimpleConditionalRates($useSimple)) {
            $tplSkuEl = $_form->addField('carriers_udtiership_v2_simple_cond_rates', 'select', array(
                'name'=>'groups[udtiership][fields][v2_simple_cond_rates][value]',
                'label'=>Mage::helper('udropship')->__('V2 Simple Conditional Rates'),
                'value'=>$tsHlp->getV2SimpleCondRates($deliveryType)
            ));
            $renderer = Mage::app()->getLayout()->createBlock('udtiership/adminhtml_systemConfigField_v2_simpleCondRates');
        } else {
            $tplSkuEl = $_form->addField('carriers_udtiership_v2_rates', 'select', array(
                'name'=>'groups[udtiership][fields][v2_rates][value]',
                'label'=>Mage::helper('udropship')->__('V2 Rates'),
                'value'=>$tsHlp->getV2Rates($deliveryType)
            ));
            $renderer = Mage::app()->getLayout()->createBlock('udtiership/adminhtml_systemConfigField_v2_rates');
        }
        $renderer
            ->setDeliveryType($deliveryType)
            ->setCtCost($ctCost)
            ->setCtAdditional($ctAdditional)
            ->setCtHandling($ctHandling)
            ->setHandlingApply($handlingApply)
            ->setCalculationMethod($calculationMethod)
        ;
        Mage::app()->getResponse()->setBody($renderer->getElementHtml($tplSkuEl));
    }

    public function loadVendorRatesAction()
    {
        $tsHlp = Mage::helper('udtiership');
        $deliveryType = $this->getRequest()->getParam('delivery_type');
        $vId = $this->getRequest()->getParam('vendor_id');
        if (!Mage::helper('udtiership')->isV2Rates() || !$deliveryType) {
            Mage::app()->getResponse()->setBody('');
            return ;
        }
        $_form = new Varien_Data_Form();
        $extraCond = array(
            '__use_vendor'=>true,
        );
        if (!empty($vId)) {
            $extraCond['vendor_id=?']=$vId;
        } else {
            $extraCond[]=new Zend_Db_Expr('false');
        }
        if (Mage::helper('udtiership')->isV2SimpleRates()) {
            $ratesEl = $_form->addField('tiership_v2_simple_rates', 'select', array(
                'name'=>'tiership_v2_simple_rates',
                'label'=>Mage::helper('udropship')->__('V2 Simple First/Additional Rates'),
                'value'=>$tsHlp->getV2SimpleRates($deliveryType, $extraCond)
            ));
            $renderer = Mage::app()->getLayout()->createBlock('udtiership/adminhtml_vendorEditTab_shippingRates_v2_renderer_simpleRates');
        } elseif (Mage::helper('udtiership')->isV2SimpleConditionalRates()) {
            $ratesEl = $_form->addField('tiership_v2_simple_cond_rates', 'select', array(
                'name'=>'tiership_v2_simple_cond_rates',
                'label'=>Mage::helper('udropship')->__('V2 Simple Conditional Rates'),
                'value'=>$tsHlp->getV2SimpleCondRates($deliveryType, $extraCond)
            ));
            $renderer = Mage::app()->getLayout()->createBlock('udtiership/adminhtml_vendorEditTab_shippingRates_v2_renderer_simpleCondRates');
        } else {
            $ratesEl = $_form->addField('tiership_v2_rates', 'select', array(
                'name'=>'tiership_v2_rates',
                'label'=>Mage::helper('udropship')->__('V2 Rates'),
                'value'=>$tsHlp->getV2Rates($deliveryType, $extraCond)
            ));
            $renderer = Mage::app()->getLayout()->createBlock('udtiership/adminhtml_vendorEditTab_shippingRates_v2_renderer_rates');
        }
        $ratesEl->setDeliveryType($deliveryType);
        $renderer->setDeliveryType($deliveryType);
        Mage::app()->getResponse()->setBody($renderer->render($ratesEl));
    }
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/udropship/vendor');
    }
}