<?php
class Kitnekaa_Credittransfer_Block_Adminhtml_Credittransfer_Renderer_Sendmail extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
 
public function render(Varien_Object $row)
{
//$value =  $row->getData($this->getColumn()->getIndex());
$customer_id = $row->getCustomerId();

$form_key = Mage::getSingleton('core/session')->getFormKey(); 
 $ab = Mage::helper("adminhtml")->getUrl("*/credittransfer/sendverificationmail", array('customer_id' => $customer_id,'form_key' => $form_key));
//$url  = Mage::helper('adminhtml')->getUrl('ad/credittransfer/sendverificationmail', array('form_key' => $form_key));
$b = '<a href="'.$ab.'">Send Mail</a>'; 
return $b;
//return 'value is '.print_r($row).'end'; 
}

}