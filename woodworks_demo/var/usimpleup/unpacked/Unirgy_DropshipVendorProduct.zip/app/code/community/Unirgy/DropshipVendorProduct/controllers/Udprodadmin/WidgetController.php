<?php

class Unirgy_DropshipVendorProduct_Udprodadmin_WidgetController extends Mage_Core_Controller_Front_Action
{
    protected $_oldStoreId;
    public function preDispatch()
    {
        Mage::register('uvp_url_store', Mage::app()->getStore(), true);
        $useSidXpath = Mage_Core_Model_Session_Abstract::XML_PATH_USE_FRONTEND_SID;
        $oldUseSid = Mage::getStoreConfig($useSidXpath);
        if ($this->getRequest()->getActionName() == 'categoriesJson') {
            Mage::app()->getStore()->setConfig($useSidXpath, 1);
        }
        parent::preDispatch();
        if ($this->getRequest()->getActionName() == 'categoriesJson') {
            Mage::app()->getStore()->setConfig($useSidXpath, $oldUseSid);
            $this->_oldStoreId = Mage::app()->getStore()->getId();
            Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
        }
        return $this;
    }
    public function categoriesJsonAction()
    {
        if ($categoryId = (int) $this->getRequest()->getPost('id')) {
            $this->getRequest()->setParam('id', $categoryId);

            if (!$category = $this->_initCategory()) {
                return;
            }
            $this->getResponse()->setBody(
                $this->getLayout()->createBlock('adminhtml/catalog_category_tree')
                    ->getTreeJson($category)
            );
        }
    }

    protected function _initCategory()
    {
        $categoryId = (int) $this->getRequest()->getParam('id',false);

        $storeId    = (int) $this->getRequest()->getParam('store');

        $category = Mage::getModel('catalog/category');
        $category->setStoreId($storeId);

        if ($categoryId) {
            $category->load($categoryId);
            if ($storeId) {
                $rootId = Mage::app()->getStore($storeId)->getRootCategoryId();
                if (!in_array($rootId, $category->getPathIds())) {
                    $this->_redirect('*/*/', array('_current'=>true, 'id'=>null));
                    return false;
                }
            }
        }

        Mage::register('category', $category);
        Mage::register('current_category', $category);
        return $category;
    }
}
