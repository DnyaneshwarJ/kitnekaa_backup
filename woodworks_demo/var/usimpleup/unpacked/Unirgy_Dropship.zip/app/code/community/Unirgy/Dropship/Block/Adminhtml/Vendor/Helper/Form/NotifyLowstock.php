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

class Unirgy_Dropship_Block_Adminhtml_Vendor_Helper_Form_NotifyLowstock extends Varien_Data_Form_Element_Select
{
    public function getAfterElementHtml()
    {
        $html = parent::getAfterElementHtml();
        $html .= '
<script type="text/javascript">
var switchNotifyLowstockSelect = function() {
	if ($("notify_lowstock").value==1) {
		$("notify_lowstock_qty").up("tr").show()
		$("notify_lowstock_qty").enable()
	} else {
		$("notify_lowstock_qty").up("tr").hide()
		$("notify_lowstock_qty").disable()
	}
}
$("notify_lowstock").observe("change", switchNotifyLowstockSelect)
document.observe("dom:loaded", switchNotifyLowstockSelect)
</script>        	
        ';
        return $html;
    }
}

