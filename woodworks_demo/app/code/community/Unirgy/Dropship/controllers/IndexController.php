<?php

class Unirgy_Dropship_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $this->_forward('index', 'vendor');
    }
    public function vendorAutocompleteAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()
                ->createBlock('udropship/vendor_autocomplete')
                ->setVendorPrefix($this->getRequest()->getParam('vendor_name'))
                ->toHtml()
        );
    }
    public function categoriesJsonAction()
    {
        Mage::helper('udropship')->disableJrdEmptyCatEvent();
        $oldStoreId = Mage::app()->getStore()->getId();
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
        $idName = $this->getRequest()->getParam('id_name');
        $nameName = $this->getRequest()->getParam('name_name');
        $idsString = $this->getRequest()->getParam('ids_string');
        $idsString = null === $idsString ? '' : $idsString;
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('udropship/categories')
                ->setIdName($idName)
                ->setNameName($nameName)
                ->setForcedIdsString($idsString)
                ->getCategoryChildrenJson($this->getRequest()->getParam('category'))
        );
        Mage::app()->setCurrentStore($oldStoreId);
    }
}