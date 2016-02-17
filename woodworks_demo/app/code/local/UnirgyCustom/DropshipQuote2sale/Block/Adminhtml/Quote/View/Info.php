<?php

class UnirgyCustom_DropshipQuote2sale_Block_Adminhtml_Quote_View_Info extends Bobcares_Quote2Sales_Block_Adminhtml_Quote_View_Info
{
    public function __construct()
    {
        if ($this->getQuote()->getVendorId() !=Mage::helper('udquote2sale')->getVendorId() && Mage::helper('udquote2sale')->isSeller()) {
            $pageId = Mage::getStoreConfig(Mage_Cms_Helper_Page::XML_PATH_NO_ROUTE_PAGE);
            if (!Mage::helper('cms/page')->renderPage($this, $pageId)) {
                $this->_forward('defaultNoRoute');
            }
        }
        else
        {
            parent::__construct();
        }
    }
}
