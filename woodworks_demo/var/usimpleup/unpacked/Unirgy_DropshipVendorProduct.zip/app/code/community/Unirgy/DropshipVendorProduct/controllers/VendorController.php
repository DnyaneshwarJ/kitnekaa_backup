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

require_once "app/code/community/Unirgy/Dropship/controllers/VendorController.php";

class Unirgy_DropshipVendorProduct_VendorController extends Unirgy_Dropship_VendorController
{
    public function indexAction()
    {
        $this->_forward('products');
    }
    public function productsAction()
    {
        $session = Mage::getSingleton('udropship/session');
        $session->setUdprodLastGridUrl(Mage::getUrl('*/*/*', array('_current'=>true)));
        $this->_renderPage(null, 'udprod');
    }
    protected function _checkProduct($productId=null)
    {
        Mage::helper('udprod')->checkProduct($productId);
        return $this;
    }
    public function productDeleteAction()
    {
        $session = Mage::getSingleton('udropship/session');
        if (!Mage::getStoreConfigFlag('udprod/general/allow_remove')) {
            $session->addError(Mage::helper('udropship')->__('Forbidden'));
        } else {
            $session = Mage::getSingleton('udropship/session');
            $oldStoreId = Mage::app()->getStore()->getId();
            try {
                Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
                $this->_checkProduct();
                $pId = Mage::app()->getRequest()->getParam('id');
                $simplePids = Mage::helper('udropship/catalog')->getCfgSimplePids($pId);
                Mage::getModel('udprod/product')->setId($pId)->delete();
                if (!empty($simplePids) && is_array($simplePids)) {
                    foreach ($simplePids as $simplePid) {
                        Mage::getModel('udprod/product')->setId($simplePid)->delete();
                    }
                }
                $session->addSuccess(Mage::helper('udropship')->__('Product was deleted'));
                Mage::app()->setCurrentStore($oldStoreId);
            } catch (Exception $e) {
                Mage::app()->setCurrentStore($oldStoreId);
                $session->addError($e->getMessage());
            }
        }
        $this->_redirectAfterPost();
    }
    public function productEditAction()
    {
        $session = Mage::getSingleton('udropship/session');
        $oldStoreId = Mage::app()->getStore()->getId();
        try {
            Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
            $this->_checkProduct();
            Mage::app()->setCurrentStore($oldStoreId);
            if (Mage::helper('udropship')->isWysiwygAllowed()) {
                $this->_renderPage(array('default', 'uwysiwyg_editor', 'uwysiwyg_editor_js'), 'udprod');
            } else {
                $this->_renderPage(null, 'udprod');
            }
        } catch (Exception $e) {
            Mage::app()->setCurrentStore($oldStoreId);
            $session->addError($e->getMessage());
            $this->_redirectAfterPost();
        }
    }
    public function productNewAction()
    {
        $session = Mage::getSingleton('udropship/session');
        Mage::app()->getRequest()->setParam('id', null);
        try {
            if (Mage::helper('udropship')->isWysiwygAllowed()) {
                $this->_renderPage(array('default', 'uwysiwyg_editor', 'uwysiwyg_editor_js'), 'udprod');
            } else {
                $this->_renderPage(null, 'udprod');
            }
        } catch (Exception $e) {
            $session->addError($e->getMessage());
            $this->_redirectAfterPost();
        }
    }
    public function productPostAction()
    {
        $session = Mage::getSingleton('udropship/session');
        $v = Mage::getSingleton('udropship/session')->getVendor();
        $hlp = Mage::helper('udropship');
        $prHlp = Mage::helper('udprod');
        $r = $this->getRequest();
        $oldStoreId = Mage::app()->getStore()->getId();
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
        if ($r->isPost()) {
            try {
                $prod = $this->_initProduct();
                $isNew = !$prod->getId();
                if (!Mage::getStoreConfigFlag('udprod/general/disable_name_check')) {
                    $ufName = $prod->formatUrlKey($prod->getName());
                    if (!trim($ufName)) {
                        Mage::throwException(Mage::helper('udropship')->__('Product name is invalid'));
                    }
                }
                $prHlp->checkUniqueVendorSku($prod, $v);
                if ($isNew) {
                    $prod->setUdprodIsNew(true);
                }
                if ($downloadable = $r->getPost('downloadable')) {
                    $prod->setDownloadableData($downloadable);
                }
                if ($links = $this->getRequest()->getPost('links')) {
                    if (isset($links['grouped'])) {
                        $prod->setGroupedLinkData(Mage::helper('adminhtml/js')->decodeGridSerializedInput($links['grouped']));
                    }
                }
                $canSaveCustOpt = $prod->getCanSaveCustomOptions();
                $custOptAll = array();
                if (!$isNew && $canSaveCustOpt) {
                    $__custOptAll = $prod->getOptions();
                    foreach ($__custOptAll as $__custOpt) {
                        $__cov = $__custOpt->getData();
                        if ($__custOpt->getGroupByType() == Mage_Catalog_Model_Product_Option::OPTION_GROUP_SELECT) {
                            foreach ($__custOpt->getValues() as $__optValue) {
                                $__cov['optionValues'][] = $__optValue->getData();
                            }
                        }
                        $custOptAll[] = $__cov;
                    }
                }
                $prod->save();
                $prHlp->processAfterSave($prod);
                $prHlp->processUdmultiPost($prod, $v);
                if ($isNew) {
                    $prHlp->processNewConfigurable($prod, $v);
                }
                $prHlp->processQuickCreate($prod, $isNew);
                if (!$isNew && $canSaveCustOpt) {
                    if ($canSaveCustOpt) {
                        $custOptAllNew = array();
                        $prod->uclearOptions();
                        if ($prod->getHasOptions()) {
                            foreach ($prod->getProductOptionsCollection() as $option) {
                                $option->setProduct($prod);
                                $prod->addOption($option);
                            }
                        }
                        $__custOptAll = $prod->getOptions();
                        foreach ($__custOptAll as $__custOpt) {
                            $__cov = $__custOpt->getData();
                            if ($__custOpt->getGroupByType() == Mage_Catalog_Model_Product_Option::OPTION_GROUP_SELECT) {
                                foreach ($__custOpt->getValues() as $__optValue) {
                                    $__cov['optionValues'][] = $__optValue->getData();
                                }
                            }
                            $custOptAllNew[] = $__cov;
                        }
                        if ($custOptAllNew!=$custOptAll) {
                            Mage::helper('udprod')->setNeedToUnpublish($prod, 'custom_options_changed');
                        }
                    }
                }
                $prHlp->reindexProduct($prod);
                $session->addSuccess(Mage::helper('udropship')->__('Product has been saved'));
            } catch (Exception $e) {
                $session->setUdprodFormData($r->getPost('product'));
                $session->addError($e->getMessage());
            }
        }
        Mage::app()->setCurrentStore($oldStoreId);
        $this->_redirectAfterPost(@$prod);
    }
    
    protected function _redirectAfterPost($prod=null)
    {
        $session = Mage::getSingleton('udropship/session');
        $hlp = Mage::helper('udropship');
        $r = $this->getRequest();
        if (!$r->getParam('continue_edit')) {
            if ($session->getUdprodLastGridUrl()) {
                $this->_redirectUrl($session->getUdprodLastGridUrl());
            } else {
                $this->_redirect('udprod/vendor/products');
            }
        } else {
            if (isset($prod) && $prod->getId()) {
                $this->_redirect('udprod/vendor/productEdit', array('id'=>$prod->getId()));
            } else {
                $this->_redirect('udprod/vendor/productNew', array('_current'=>true));
            }
        }
    }
    protected function _initProduct()
    {
        $r = $this->getRequest();
        $v = Mage::getSingleton('udropship/session')->getVendor();
        $productId  = (int) $this->getRequest()->getParam('id');
        $productData = $r->getPost('product');
        $product = Mage::helper('udprod')->initProductEdit(array(
            'id'   => $productId,
            'data' => $productData,
            'vendor' => $v
        ));
        if (isset($productData['options'])) {
            $product->setProductOptions($productData['options']);
        }
        $product->setCanSaveCustomOptions(
            (bool)$this->getRequest()->getPost('affect_product_custom_options')
        );
        return $product;
    }
    /*
    public function categoriesJsonAction()
    {
        $r = Mage::app()->getRequest();
        $oldStoreId = Mage::app()->getStore()->getId();
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
        $product = Mage::helper('udprod')->initProductEdit(array(
            'id' => $r->getParam('id'),
            'vendor' => Mage::getSingleton('udropship/session'),
        ));
        Mage::register('current_product', $product);
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('udropship/categories')
                ->getCategoryChildrenJson($this->getRequest()->getParam('category'))
        );
        Mage::app()->setCurrentStore($oldStoreId);
    }
    */
    public function cfgQuickCreateAttributeAction()
    {
        $session = Mage::getSingleton('udropship/session');
        $oldStoreId = Mage::app()->getStore()->getId();
        try {
            $this->_setTheme();
            $prodBlock = Mage::app()->getLayout()->createBlock('udprod/vendor_product', 'udprod.edit', array('skip_add_head_js'=>1));
            $cfgEl = $prodBlock->getForm()->getElement('_cfg_quick_create');
            $__value = $this->getRequest()->getParam('cfg_attr_values');
            $cfgEl->setCfgAttributeValueTuple(explode(',',$__value));
            $this->getResponse()->setBody(
                $cfgEl->toHtml()
            );
        } catch (Exception $e) {
            Mage::app()->setCurrentStore($oldStoreId);
            $this->returnResult(array(
                'error'=>true,
                'message' => $e->getMessage(),
            ));
        }
    }
    public function superGroupGridOnlyAction()
    {
        Mage::helper('udropship/catalog')->setDesignStore(0, 'adminhtml');
        $prod = $this->_initProduct();
        Mage::register('current_product', $prod);
        $grouped = $this->getLayout()
            ->createBlock('udprod/vendor_product_renderer_groupedAssocProducts', 'admin.product.options')
            ->setSkipSerializer(true)
            ->setProductsGrouped($this->getRequest()->getPost('products_grouped', null));
        $this->getResponse()->setBody(
            $grouped->toHtml()
        );
    }
    public function returnResult($result)
    {
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
    protected $_oldStoreId;
    public function preDispatch()
    {
        Mage::register('uvp_url_store', Mage::app()->getStore(), true);
        $useSidXpath = Mage_Core_Model_Session_Abstract::XML_PATH_USE_FRONTEND_SID;
        $oldUseSid = Mage::getStoreConfig($useSidXpath);
        if ($this->getRequest()->getActionName() == 'upload') {
            Mage::app()->getStore()->setConfig($useSidXpath, 1);
        }
        parent::preDispatch();
        if ($this->getRequest()->getActionName() == 'upload') {
            Mage::app()->getStore()->setConfig($useSidXpath, $oldUseSid);
            $this->_oldStoreId = Mage::app()->getStore()->getId();
            Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
        }
        return $this;
    }
    public function uploadAction()
    {
        try {
            $uploader = new Mage_Core_Model_File_Uploader($this->getRequest()->getParam('image_field', 'image'));
            $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
            $uploader->addValidateCallback('catalog_product_image',
                Mage::helper('catalog/image'), 'validateUploadFile');
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);
            $result = $uploader->save(
                Mage::getSingleton('catalog/product_media_config')->getBaseTmpMediaPath()
            );
            Mage::dispatchEvent('catalog_product_gallery_upload_image_after', array(
                'result' => $result,
                'action' => $this
            ));
            $result['tmp_name'] = str_replace(DS, "/", $result['tmp_name']);
            $result['path'] = str_replace(DS, "/", $result['path']);

            $result['url'] = Mage::getSingleton('catalog/product_media_config')->getTmpMediaUrl($result['file']);
            //$result['file'] = $result['file'] . '.tmp';
            $result['file'] = $result['file'];
            $result['cookie'] = array(
                'name'     => session_name(),
                'value'    => $this->_getSession()->getSessionId(),
                'lifetime' => $this->_getSession()->getCookieLifetime(),
                'path'     => $this->_getSession()->getCookiePath(),
                'domain'   => $this->_getSession()->getCookieDomain()
            );

        } catch (Exception $e) {
            $result = array(
                'error' => $e->getMessage(),
                'errorcode' => $e->getCode());
        }
        usleep(10);
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        if ($this->_oldStoreId) Mage::app()->setCurrentStore($this->_oldStoreId);
    }
    public function downloadableUploadAction()
    {
        $type = $this->getRequest()->getParam('type');
        $tmpPath = '';
        if ($type == 'samples') {
            $tmpPath = Mage_Downloadable_Model_Sample::getBaseTmpPath();
        } elseif ($type == 'links') {
            $tmpPath = Mage_Downloadable_Model_Link::getBaseTmpPath();
        } elseif ($type == 'link_samples') {
            $tmpPath = Mage_Downloadable_Model_Link::getBaseSampleTmpPath();
        }
        $result = array();
        try {
            $uploader = new Mage_Core_Model_File_Uploader($type);
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);
            $result = $uploader->save($tmpPath);

            /**
             * Workaround for prototype 1.7 methods "isJSON", "evalJSON" on Windows OS
             */
            $result['tmp_name'] = str_replace(DS, "/", $result['tmp_name']);
            $result['path'] = str_replace(DS, "/", $result['path']);

            /*
            if (isset($result['file'])) {
                $fullPath = rtrim($tmpPath, DS) . DS . ltrim($result['file'], DS);
                Mage::helper('core/file_storage_database')->saveFile($fullPath);
            }
            */

            $result['cookie'] = array(
                'name'     => session_name(),
                'value'    => $this->_getSession()->getSessionId(),
                'lifetime' => $this->_getSession()->getCookieLifetime(),
                'path'     => $this->_getSession()->getCookiePath(),
                'domain'   => $this->_getSession()->getCookieDomain()
            );
        } catch (Exception $e) {
            $result = array('error'=>$e->getMessage(), 'errorcode'=>$e->getCode());
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
}