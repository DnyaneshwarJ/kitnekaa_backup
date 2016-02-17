<?php

class Unirgy_DropshipTierShipping_Block_Adminhtml_VendorEditTab_ShippingRates_V2_Form_DeliveryTypeSelector extends Varien_Data_Form_Element_Select
{
    public function getAfterElementHtml()
    {
        $vendor = Mage::registry('vendor_data');
        $vId = $vendor ? $vendor->getId() : 0;
        $html = parent::getAfterElementHtml();
        $htmlId = $this->getHtmlId();

        if (Mage::helper('udtiership')->isV2SimpleRates()) {
            $targetHtmlId = str_replace('delivery_type_selector', 'v2_simple_rates', $htmlId).'_container';
        } elseif (Mage::helper('udtiership')->isV2SimpleConditionalRates()) {
            $targetHtmlId = str_replace('delivery_type_selector', 'v2_simple_cond_rates', $htmlId).'_container';
        } else {
            $targetHtmlId = str_replace('delivery_type_selector', 'v2_rates', $htmlId).'_container';
        }

        $targetUrl = Mage::getModel('adminhtml/url')->getUrl('adminhtml/udtiershipadmin_index/loadVendorRates', array('delivery_type'=>'DELIVERYTYPE','vendor_id'=>$vId));
        $html .= "
            <script type=\"text/javascript\">
                Event.observe('$htmlId', 'change', function(){
                    if (\$F('$htmlId')) {
                        var targetHtmlId = '$targetHtmlId';
                        new Ajax.Updater(targetHtmlId, '$targetUrl'.replace('DELIVERYTYPE', \$F('$htmlId')), {asynchronous:true, evalScripts:true});
                    }
                });
            </script>";
        return $html;
    }
}