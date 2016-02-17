<?php

class Unirgy_Dropship_Block_Adminhtml_Vendor_Helper_GridRenderer_StockQty extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Number
{
    public function render(Varien_Object $row)
    {
        $html = parent::render($row);
        $hlp = Mage::helper('udropship');
        $vId = $this->getColumn()->getVendorId() ? $this->getColumn()->getVendorId() : $row->getVendorId();
        if ($this->getColumn()->getEditable()
            && Mage::helper('udropship')->isUdmultiAvailable()
            && ($urq = Mage::getSingleton('udropship/source')->getVendorsColumn('use_reserved_qty'))
            && !empty($urq[$vId]) && $this->getColumn()->getUrqId()
        ) {
            $rQty = $row->getReservedQty()*1;
            $htmlId = '_'.md5(uniqid(microtime(), true));
            $lbl = Mage::helper('udropship')->__('Includes Reserved Qty')." ($rQty)";
            $html .=<<<EOT
<nobr><br />
<input id="$htmlId" type="checkbox" name="{$this->getColumn()->getUrqId()}" value="1"  /> $lbl
</nobr>
EOT;

        }
        return $html;
    }
}