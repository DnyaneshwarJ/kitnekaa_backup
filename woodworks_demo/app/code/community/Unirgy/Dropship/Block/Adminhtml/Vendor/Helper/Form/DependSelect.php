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

class Unirgy_Dropship_Block_Adminhtml_Vendor_Helper_Form_DependSelect extends Varien_Data_Form_Element_Select
{
    public function getAfterElementHtml()
    {
        $html = parent::getAfterElementHtml();
        $fc = (array)$this->getData('field_config');
        if (isset($fc['depend_fields']) && ($dependFields = (array)$fc['depend_fields'])
            || isset($fc['hide_depend_fields']) && ($hideDependFields = (array)$fc['hide_depend_fields'])
        ) {
            if (!empty($dependFields)) {
                foreach ($dependFields as &$dv) {
                    $dv = $dv!='' ? explode(',', $dv) : array('');
                }
                unset($dv);
                $dfJson = Zend_Json::encode($dependFields);
            } else {
                $dfJson = '{}';
            }
            if (!empty($hideDependFields)) {
                foreach ($hideDependFields as &$dv) {
                    $dv = $dv!='' ? explode(',', $dv) : array('');
                }
                unset($dv);
                $hideDfJson = Zend_Json::encode($hideDependFields);
            } else {
                $hideDfJson = '{}';
            }
            $html .=<<<EOT
<script type="text/javascript">
document.observe("dom:loaded", function() {
	var df = \$H($dfJson);
	var hideDf = \$H($hideDfJson);
	var enableDisable = function (pair, flag) {
        if ($(pair.key) && (trElem = $(pair.key).up("tr"))) {
            if (flag == (\$A(pair.value).indexOf($('{$this->getHtmlId()}').value) != -1)) {
                trElem.show()
                trElem.select('select').each(function(__sEl){
                    __sEl.enable();
                    if (__sEl.udSyncDependFields) {
                        __sEl.udSyncDependFields();
                    }
                });
                trElem.select('input').invoke('enable')
                trElem.select('textarea').invoke('enable')
            } else {
                trElem.hide()
                trElem.select('select').invoke('disable')
                trElem.select('input').invoke('disable')
                trElem.select('textarea').invoke('disable')
            }
        }
    }
	var syncDependFields = function() {
		df.each(function(pair){
			enableDisable(pair, true);
		});
		hideDf.each(function(pair){
			enableDisable(pair, false);
		});
	}
	$('{$this->getHtmlId()}').udSyncDependFields = syncDependFields;
    $('{$this->getHtmlId()}').observe('change', $('{$this->getHtmlId()}').udSyncDependFields.bind($('{$this->getHtmlId()}')))
    $('{$this->getHtmlId()}').udSyncDependFields()
})
</script>
EOT;
        }
        return $html;
    }
}

