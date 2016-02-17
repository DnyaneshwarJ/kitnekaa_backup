<?php

/**
 * Added by Dnyaneshwar S. Jambhulkar
 * Render Vendor name in quote grid
 */
class UnirgyCustom_DropshipQuote2sale_Block_Adminhtml_Renderer_VendorName extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    /**
     * @desc Render Vendor name in quote grid
     * @param Varien_Object $row : input data (request id)
     * @return string : The request link corresponding quote.
     */
    public function render(Varien_Object $row) {
        $vendorId = $row->getData('vendor_id');
        $vendor = Mage::helper('udropship')->getVendor($vendorId);
        if ($vendor->getVendorName()) {
            return $vendor->getVendorName();
        } else {
            return "";
        }
    }

}
