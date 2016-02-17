<?php

class Unirgy_DropshipMulti_Vendor_ProductController extends Unirgy_Dropship_Controller_VendorAbstract
{
    public function indexAction()
    {
        $this->_renderPage(null, 'stockprice');
    }

    public function saveAction()
    {
        try {
            $hlp = Mage::helper('udmulti');
            $cnt = $hlp->saveVendorProducts($this->getRequest()->getParam('vp'));
            if ($cnt) {
                Mage::getSingleton('udropship/session')->addSuccess(Mage::helper('udropship')->__($cnt==1 ? '%s product was updated' : '%s products were updated', $cnt));
            } else {
                Mage::getSingleton('udropship/session')->addNotice(Mage::helper('udropship')->__('No updates were made'));
            }
        } catch (Exception $e) {
            Mage::getSingleton('udropship/session')->addError($e->getMessage());
        }
        $this->_redirect('udmulti/vendor_product');
    }
}