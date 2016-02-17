<?php

class Unirgy_Dropship_Block_Adminhtml_Vendor_Helper_GridRenderer_SpecialPrice extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Number
{
    public function render(Varien_Object $row)
    {
        $html = parent::render($row);
        $hlp = Mage::helper('udropship');
        $calGridUrl = Mage::getDesign()->getSkinUrl('images/grid-cal.gif');
        $fromDate = $row->getData('special_from_date');
        $toDate = $row->getData('special_to_date');
        if ($fromDate) {
            $fromDate = $hlp->dateInternalToLocale($fromDate);
        }
        if ($toDate) {
            $toDate = $hlp->dateInternalToLocale($toDate);
        }
        $spLabel = Mage::helper('udropship')->__('Special Price');
        $sfdLabel = Mage::helper('udropship')->__('From Date');
        $stdLabel = Mage::helper('udropship')->__('To Date');
        $_dateFormat = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        $_calDateFormat = Varien_Date::convertZendToStrFtime($_dateFormat, true, false);
        if ($this->getColumn()->getEditable()) {
        $htmlId = '_'.md5(uniqid(microtime(), true));
        $html .=<<<EOT
<nobr><br />
$sfdLabel <input id="{$htmlId}_sfd" type="text" class="input-text" name="_special_from_date" value="$fromDate" style="width:110px !important;" />
<!--img src="$calGridUrl" alt="" class="v-middle"title="" style="" /-->
</nobr>
<script type="text/javascript">
//<![CDATA[
    Calendar.setup({
        inputField: "{$htmlId}_sfd",
        ifFormat: "$_calDateFormat",
        showsTime: false,
        button: "{$htmlId}__sfd_trig",
        align: "Bl",
        singleClick : true
    });
//]]>
</script>
</nobr>
<nobr><br />
$stdLabel <input id="{$htmlId}_std" type="text" class="input-text" name="_special_to_date" value="$toDate" style="width:110px !important;" />
<!--img src="$calGridUrl" alt="" class="v-middle"title="" style="" /-->
</nobr>
<script type="text/javascript">
//<![CDATA[
    Calendar.setup({
        inputField: "{$htmlId}_std",
        ifFormat: "$_calDateFormat",
        showsTime: false,
        button: "{$htmlId}__std_trig",
        align: "Bl",
        singleClick : true
    });
//]]>
</script>
</nobr>
EOT;
        }
        return $html;
    }
}