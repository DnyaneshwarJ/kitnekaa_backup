<?php 
class Neo_AdminFormUpload_Block_Adminhtml_Form_Helper_Image extends Varien_Data_Form_Element_Image
{
    //make your renderer allow "multiple" attribute
    public function getHtmlAttributes(){
        return array_merge(parent::getHtmlAttributes(), array('multiple'));
    }
}