<?php

class Unirgy_DropshipVendorProduct_Block_Adminhtml_SystemConfigField_TypeOfProductSelector extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    public function __construct()
    {
        parent::__construct();
        if (($head = Mage::app()->getLayout()->getBlock('head'))) {
            $head->setCanLoadExtJs(true);
        }
    }
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $elHtml = $element->getElementHtml();
        $htmlId = $element->getHtmlId();
        $targetHtmlId = str_replace('type_of_product', 'value', $htmlId).'_container';
        $targetUrl = $this->getUrl('adminhtml/udprodadmin_udprod/loadTemplateSku', array('_query'=>array('type_of_product'=>'TYPEOFPRODUCT')));
        $elHtml .= "
            <script type=\"text/javascript\">
                Event.observe('$htmlId', 'change', function(){
                    if (\$F('$htmlId')) {
                        new Ajax.Updater('$targetHtmlId', '$targetUrl'.replace('TYPEOFPRODUCT', encodeURIComponent(\$F('$htmlId'))), {asynchronous:true, evalScripts:true});
                    }
                });
            </script>";
        return $elHtml;
    }
}