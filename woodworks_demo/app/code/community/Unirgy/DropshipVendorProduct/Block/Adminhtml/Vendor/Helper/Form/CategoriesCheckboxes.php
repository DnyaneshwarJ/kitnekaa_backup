<?php

class Unirgy_DropshipVendorProduct_Block_Adminhtml_Vendor_Helper_Form_CategoriesCheckboxes extends Varien_Data_Form_Element_Abstract
{
	public function getHtml()
    {
        $catIds = array();
        if (($v = Mage::registry('vendor_data')) && $v->getIsLimitCategories() && ($lc = $v->getLimitCategories())) {
            $catIds = explode(',', implode(',', (array)$lc));
        }
        $this->_renderer = Mage::getSingleton('core/layout')
            ->createBlock('udprod/adminhtml_vendor_helper_renderer_categoriesCheckboxes')
            ->setCategoryIds($catIds);
        return parent::getHtml();
    }
}