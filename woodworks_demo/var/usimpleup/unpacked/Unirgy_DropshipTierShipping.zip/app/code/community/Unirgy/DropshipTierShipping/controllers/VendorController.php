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
 * @package    Unirgy_Dropship
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

require_once "app/code/community/Unirgy/Dropship/controllers/VendorController.php";

class Unirgy_DropshipTierShipping_VendorController extends Unirgy_Dropship_VendorController
{
    public function ratesAction()
{
    $this->_renderPage(null, 'tiership_rates');
}
    public function ratesPostAction()
    {
        $session = Mage::getSingleton('udropship/session');
        $hlp = Mage::helper('udropship');
        $r = $this->getRequest();
        if ($r->isPost()) {
            $p = $r->getPost();
            try {
                $v = $session->getVendor();
                $v->setTiershipRates($p['tiership_rates']);
                $v->save();
#echo "<pre>"; print_r($v->debug()); exit;
                $session->addSuccess('Rates has been saved');
            } catch (Exception $e) {
                $session->addError($e->getMessage());
            }
        }
        $this->_redirect('udtiership/vendor/rates');
    }
    public function simpleratesAction()
    {
        $this->_renderPage(null, 'tiership_simple_rates');
    }
    public function simpleratesPostAction()
    {
        $session = Mage::getSingleton('udropship/session');
        $hlp = Mage::helper('udropship');
        $r = $this->getRequest();
        if ($r->isPost()) {
            $p = $r->getPost();
            try {
                $v = $session->getVendor();
                $v->setTiershipSimpleRates($p['tiership_simple_rates']);
                $v->save();
#echo "<pre>"; print_r($v->debug()); exit;
                $session->addSuccess('Rates has been saved');
            } catch (Exception $e) {
                $session->addError($e->getMessage());
            }
        }
        $this->_redirect('udtiership/vendor/simplerates');
    }

    public function v2ratesAction()
    {
        $this->_renderPage(null, 'tiership_v2_rates');
    }
    public function v2ratesPostAction()
    {
        $session = Mage::getSingleton('udropship/session');
        $hlp = Mage::helper('udropship');
        $r = $this->getRequest();
        if ($r->isPost()) {
            $p = $r->getPost();
            try {
                $v = $session->getVendor();
                $v->setData('tiership_v2_rates', @$p['tiership_v2_rates']);
                $v->setData('tiership_v2_simple_rates', @$p['tiership_v2_simple_rates']);
                $v->setData('tiership_v2_simple_cond_rates', @$p['tiership_v2_simple_cond_rates']);
                Mage::helper('udtiership')->saveVendorV2Rates($v->getId(), $v->getData('tiership_v2_rates'));
                Mage::helper('udtiership')->saveVendorV2SimpleRates($v->getId(), $v->getData('tiership_v2_simple_rates'));
                Mage::helper('udtiership')->saveVendorV2SimpleCondRates($v->getId(), $v->getData('tiership_v2_simple_cond_rates'));
#echo "<pre>"; print_r($v->debug()); exit;
                $session->addSuccess('Rates has been saved');
            } catch (Exception $e) {
                $session->addError($e->getMessage());
            }
        }
        $this->_redirect('udtiership/vendor/v2rates');
    }
    public function loadVendorRatesAction()
    {
        $session = Mage::getSingleton('udropship/session');
        $tsHlp = Mage::helper('udtiership');
        $deliveryType = $this->getRequest()->getParam('delivery_type');
        $vId = $session->getVendorId();
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
            $renderer = Mage::app()->getLayout()->createBlock('udtiership/vendor_v2_simpleRates');
        } elseif (Mage::helper('udtiership')->isV2SimpleConditionalRates()) {
            $ratesEl = $_form->addField('tiership_v2_simple_cond_rates', 'select', array(
                'name'=>'tiership_v2_simple_cond_rates',
                'label'=>Mage::helper('udropship')->__('V2 Simple Conditional Rates'),
                'value'=>$tsHlp->getV2SimpleCondRates($deliveryType, $extraCond)
            ));
            $renderer = Mage::app()->getLayout()->createBlock('udtiership/vendor_v2_simpleCondRates');
        } else {
            $ratesEl = $_form->addField('tiership_v2_rates', 'select', array(
                'name'=>'tiership_v2_rates',
                'label'=>Mage::helper('udropship')->__('V2 Rates'),
                'value'=>$tsHlp->getV2Rates($deliveryType, $extraCond)
            ));
            $renderer = Mage::app()->getLayout()->createBlock('udtiership/vendor_v2_rates');
        }
        $ratesEl->setDeliveryType($deliveryType);
        $renderer->setDeliveryType($deliveryType);
        Mage::app()->getResponse()->setBody($renderer->render($ratesEl));
    }
}