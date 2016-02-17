<?php

class Unirgy_DropshipVendorProduct_Udprodadmin_UdprodController extends Mage_Adminhtml_Controller_Action
{
    public function loadTemplateSkuAction()
    {
        $typeOfProduct = $this->getRequest()->getParam('type_of_product');
        $_form = new Varien_Data_Form();
        $tplSku = Mage::getStoreConfig('udprod/template_sku/value');
        $tplSku = empty($tplSku) ? array() : $tplSku;
        if (!is_array($tplSku)) {
            $tplSku = unserialize($tplSku);
        }
        $tplSkuEl = $_form->addField('udprod_template_sku_value', 'select', array(
            'name'=>'groups[template_sku][fields][value][value]',
            'label'=>Mage::helper('udropship')->__('Template Sku'),
            'value'=>$tplSku,
        ));
        $renderer = Mage::app()->getLayout()->createBlock('udprod/adminhtml_systemConfigField_templateSku');
        $renderer->setTypeOfProduct($typeOfProduct);
        Mage::app()->getResponse()->setBody($renderer->getElementHtml($tplSkuEl));
    }
}