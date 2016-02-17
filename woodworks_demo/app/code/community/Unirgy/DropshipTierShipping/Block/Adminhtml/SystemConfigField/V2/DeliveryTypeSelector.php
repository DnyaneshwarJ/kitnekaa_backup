<?php

class Unirgy_DropshipTierShipping_Block_Adminhtml_SystemConfigField_V2_DeliveryTypeSelector extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $elHtml = $element->getElementHtml();
        $htmlId = $element->getHtmlId();
        $useSimpleHtmlId = str_replace('delivery_type_selector', 'use_simple_rates', $htmlId);
        $ctCostHtmlId = str_replace('delivery_type_selector', 'cost_calculation_type', $htmlId);
        $ctAdditionalHtmlId = str_replace('delivery_type_selector', 'additional_calculation_type', $htmlId);
        $ctHandlingHtmlId = str_replace('delivery_type_selector', 'handling_calculation_type', $htmlId);
        $handlingApplyHtmlId = str_replace('delivery_type_selector', 'handling_apply_method', $htmlId);
        $calculationMethodHtmlId = str_replace('delivery_type_selector', 'calculation_method', $htmlId);
        $simpleTargetHtmlId = str_replace('delivery_type_selector', 'v2_simple_rates', $htmlId).'_container';
        $simpleCondTargetHtmlId = str_replace('delivery_type_selector', 'v2_simple_cond_rates', $htmlId).'_container';
        $targetHtmlId = str_replace('delivery_type_selector', 'v2_rates', $htmlId).'_container';
        $targetUrl = $this->getUrl('adminhtml/udtiershipadmin_index/loadRates', array('delivery_type'=>'DELIVERYTYPE','use_simple'=>'USESIMPLE','ct_cost'=>'CTCOST','ct_additional'=>'CTADDITIONAL','ct_handling'=>'CTHANDLING','handling_apply'=>'HANDLINGAPPLY','calculation_method'=>'CALCULATIONMETHOD'));
        $elHtml .= "
            <script type=\"text/javascript\">
                Event.observe('$htmlId', 'change', function(){
                    if (\$F('$htmlId')) {
                        var _simpleTargetHtmlId = '$simpleTargetHtmlId';
                        var _simpleCondTargetHtmlId = '$simpleCondTargetHtmlId';
                        var _targetHtmlId = '$targetHtmlId';
                        var targetHtmlId, otherTargetHtmlId;
                        if (\$F('$useSimpleHtmlId') == 3) {
                            targetHtmlId = _simpleTargetHtmlId;
                            otherTargetHtmlId = [$(_targetHtmlId), $(_simpleCondTargetHtmlId)];
                        } else if (\$F('$useSimpleHtmlId') == 4) {
                            otherTargetHtmlId = [$(_simpleTargetHtmlId), $(_targetHtmlId)];
                            targetHtmlId = _simpleCondTargetHtmlId;
                        } else {
                            otherTargetHtmlId = [$(_simpleTargetHtmlId), $(_simpleCondTargetHtmlId)];
                            targetHtmlId = _targetHtmlId;
                        }
                        \$A(otherTargetHtmlId).invoke('update', '');
                        new Ajax.Updater(targetHtmlId, '$targetUrl'.replace('DELIVERYTYPE', \$F('$htmlId')).replace('USESIMPLE', \$F('$useSimpleHtmlId')).replace('CTCOST', \$F('$ctCostHtmlId')).replace('CTADDITIONAL', \$F('$ctAdditionalHtmlId')).replace('CTHANDLING', \$F('$ctHandlingHtmlId')).replace('HANDLINGAPPLY', \$F('$handlingApplyHtmlId')).replace('CALCULATIONMETHOD', \$F('$calculationMethodHtmlId')), {asynchronous:true, evalScripts:true});
                    }
                });
            </script>";
        return $elHtml;
    }
}