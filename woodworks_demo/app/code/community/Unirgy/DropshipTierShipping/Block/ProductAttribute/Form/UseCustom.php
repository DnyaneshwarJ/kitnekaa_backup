<?php

class Unirgy_DropshipTierShipping_Block_ProductAttribute_Form_UseCustom extends Varien_Data_Form_Element_Select
{
    public function getAfterElementHtml()
    {
        $html = parent::getAfterElementHtml();
        $htmlId = $this->getHtmlId();
        $ratesHtmlId = str_replace('udtiership_use_custom', 'udtiership_rates', $htmlId);
        $curProd = Mage::registry('current_product');
        if ($curProd && $curProd->getData('_edit_in_vendor')) {
            $trTag = 'li';
        } else {
            $trTag = 'tr';
        }
        $html .= <<<EOT
<script type="text/javascript">
var syncUdtiershipUseCustom = function() {
    if (\$('$ratesHtmlId') && (trElem = \$('$ratesHtmlId').up("$trTag"))) {
        if (\$F('$htmlId') && \$F('$htmlId')!='0') {
            trElem.show();
            trElem.select('select').invoke('enable');
            trElem.select('input').invoke('enable');
            trElem.select('textarea').invoke('enable');
        } else {
            trElem.hide();
            trElem.select('select').invoke('disable');
            trElem.select('input').invoke('disable');
            trElem.select('textarea').invoke('disable');
        }
	}
}
document.observe('dom:loaded', function() {
    $('$htmlId').observe('change', syncUdtiershipUseCustom);
    syncUdtiershipUseCustom();
});
</script>
EOT;
        return $html;
    }
}