<?php

class Unirgy_Dropship_Block_Adminhtml_Vendor_Helper_GridRenderer_Date extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Date
{
    public function render(Varien_Object $row)
    {
        $html = parent::render($row);
        $hlp = Mage::helper('udropship');
        if ($this->getColumn()->getEditable()) {
            $calGridUrl = Mage::getDesign()->getSkinUrl('images/grid-cal.gif');
            $date = $row->getData($this->getColumn()->getIndex());
            if ($date) {
                $date = $hlp->dateInternalToLocale($date);
            }
            $_dateFormat = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
            $_calDateFormat = Varien_Date::convertZendToStrFtime($_dateFormat, true, false);
            $htmlId = '_'.md5(uniqid(microtime(), true));
            $html .=<<<EOT
<input id="$htmlId" type="text" class="input-text" name="{$this->getColumn()->getId()}" value="$date" style="width:110px !important;" />
<!--img src="$calGridUrl" alt="" class="v-middle"title="" style="" /-->
</nobr>
<script type="text/javascript">
//<![CDATA[
    Calendar.setup({
        inputField: "$htmlId",
        ifFormat: "$_calDateFormat",
        showsTime: false,
        button: "{$htmlId}_trig",
        align: "Bl",
        singleClick : true
    });
//]]>
</script>
EOT;

        }
        return $html;
    }
}