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
 * @package    Unirgy_DropshipPo
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */
 
class Unirgy_Dropship_Block_Adminhtml_SystemConfigFormField_DependSelect extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $html = parent::render($element);
        $fc = (array)$element->getData('field_config');
        if (isset($fc['depend_fields']) && ($dependFields = (array)$fc['depend_fields'])) {
            foreach ($dependFields as &$dv) {
                $dv = explode(',', $dv);
            }
            $dfJson = Zend_Json::encode($dependFields);
            $html .=<<<EOT
<script type="text/javascript">
document.observe("dom:loaded", function() {
	var df = \$H($dfJson)
	$('{$element->getHtmlId()}')['syncDependFields'] = function() {
	    df.each(function(pair){
			if ((trElem = $("row_"+pair.key)) || $(pair.key) && (trElem = $(pair.key).up("tr"))) {
				if (\$A(pair.value).indexOf($('{$element->getHtmlId()}').value) != -1) {
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
            	if ($(pair.key) && $(pair.key)['syncDependFields']) {
            	    $(pair.key)['syncDependFields'].defer();
                }
			}
		})
	}
    $('{$element->getHtmlId()}').observe('change', $('{$element->getHtmlId()}')['syncDependFields']);
    $('{$element->getHtmlId()}')['syncDependFields'].defer();
})
</script>
EOT;
        }
        return $html;
    }
}