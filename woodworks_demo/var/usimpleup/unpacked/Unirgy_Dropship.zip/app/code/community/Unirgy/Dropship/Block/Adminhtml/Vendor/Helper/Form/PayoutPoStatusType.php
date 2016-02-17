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

class Unirgy_Dropship_Block_Adminhtml_Vendor_Helper_Form_PayoutPoStatusType extends Varien_Data_Form_Element_Select
{
    public function getAfterElementHtml()
    {
        $html = parent::getAfterElementHtml();
        $html .= '
<script type="text/javascript">
var switchPayoutPoStatusSelect = function() {
    var defStPoType = "'.(Mage::getStoreConfig('udropship/statement/statement_po_type')).'";
    var getStPoType = function(val) {
        return val == "999" ? defStPoType : val;
    }
    for (i=0; i<$("statement_po_type").options.length; i++) {
		var statusSel = $("payout_"+getStPoType($("statement_po_type").options[i].value)+"_status");
		if (statusSel) {
    		if (statusSel.id == "payout_"+getStPoType($("statement_po_type").value)+"_status" && $("payout_po_status_type").value == "payout") {
    			statusSel.up("tr").show();
    			statusSel.enable();
    		} else {
    			statusSel.up("tr").hide();
    			statusSel.disable();
    		}
		}
	}
}
document.observe("dom:loaded", function(){
    $("payout_po_status_type").observe("change", switchPayoutPoStatusSelect)
    $("statement_po_type").observe("change", switchPayoutPoStatusSelect)
    switchPayoutPoStatusSelect();
});
</script>        	
        ';
        return $html;
    }
}

