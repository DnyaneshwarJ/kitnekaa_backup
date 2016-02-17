<?php

class Unirgy_Dropship_Vendor_Wysiwyg_ImagesController extends Unirgy_Dropship_Controller_VendorAbstract
{
    protected function _initAction()
    {
        $this->_setTheme();
        $this->getStorage();
        return $this;
    }

    public function indexAction()
    {
        $storeId = (int) $this->getRequest()->getParam('store');

        try {
            Mage::helper('udropship/wysiwyg_images')->getCurrentPath();
        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        $this->_initAction()->loadLayout('udropship_overlay_popup');
        $block = $this->getLayout()->getBlock('wysiwyg_images.js');
        if ($block) {
            $block->setStoreId($storeId);
        }
        $this->renderLayout();
    }

    public function treeJsonAction()
    {
        try {
            $this->_initAction();
            $this->getResponse()->setBody(
                $this->getLayout()->createBlock('udropship/vendor_wysiwyg_images_tree')
                    ->getTreeJson()
            );
        } catch (Exception $e) {
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array()));
        }
    }

    public function contentsAction()
    {
        try {
            $this->_initAction()->_saveSessionCurrentPath();
            $this->loadLayout('empty');
            $this->renderLayout();
        } catch (Exception $e) {
            $result = array('error' => true, 'message' => $e->getMessage());
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }

    public function newFolderAction()
    {
        try {
            $this->_initAction();
            $name = $this->getRequest()->getPost('name');
            $path = $this->getStorage()->getSession()->getCurrentPath();
            $result = $this->getStorage()->createDirectory($name, $path);
        } catch (Exception $e) {
            $result = array('error' => true, 'message' => $e->getMessage());
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function deleteFolderAction()
    {
        try {
            $path = $this->getStorage()->getSession()->getCurrentPath();
            $this->getStorage()->deleteDirectory($path);
        } catch (Exception $e) {
            $result = array('error' => true, 'message' => $e->getMessage());
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }

    public function deleteFilesAction()
    {
        try {
            if (!$this->getRequest()->isPost()) {
                throw new Exception ('Wrong request.');
            }
            $files = Mage::helper('core')->jsonDecode($this->getRequest()->getParam('files'));

            /** @var $helper Mage_Cms_Helper_Wysiwyg_Images */
            $helper = Mage::helper('udropship/wysiwyg_images');
            $path = $this->getStorage()->getSession()->getCurrentPath();
            foreach ($files as $file) {
                $file = $helper->idDecode($file);
                $_filePath = realpath($path . DS . $file);
                if (strpos($_filePath, realpath($path)) === 0 &&
                    strpos($_filePath, realpath($helper->getStorageRoot())) === 0
                ) {
                    $this->getStorage()->deleteFile($path . DS . $file);
                }
            }
        } catch (Exception $e) {
            $result = array('error' => true, 'message' => $e->getMessage());
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }

    public function preDispatch()
    {
        $useSidXpath = Mage_Core_Model_Session_Abstract::XML_PATH_USE_FRONTEND_SID;
        $oldUseSid = Mage::getStoreConfig($useSidXpath);
        if ($this->getRequest()->getActionName() == 'upload') {
            Mage::app()->getStore()->setConfig($useSidXpath, 1);
        }
        parent::preDispatch();
        if ($this->getRequest()->getActionName() == 'upload') {
            Mage::app()->getStore()->setConfig($useSidXpath, $oldUseSid);
        }
        return $this;
    }

    public function uploadAction()
    {
        try {
            $result = array();
            $this->_initAction();
            $targetPath = $this->getStorage()->getSession()->getCurrentPath();
            $result = $this->getStorage()->uploadFile($targetPath, $this->getRequest()->getParam('type'));
            $result['tmp_name'] = str_replace(DS, "/", $result['tmp_name']);
            $result['path'] = str_replace(DS, "/", $result['path']);
        } catch (Exception $e) {
            $result = array('error' => $e->getMessage(), 'errorcode' => $e->getCode());
        }
        //usleep(10);
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));

    }

    public function onInsertAction()
    {
        $helper = Mage::helper('udropship/wysiwyg_images');
        $storeId = $this->getRequest()->getParam('store');

        $filename = $this->getRequest()->getParam('filename');
        $filename = $helper->idDecode($filename);
        $asIs = $this->getRequest()->getParam('as_is');

        Mage::helper('catalog')->setStoreId($storeId);
        $helper->setStoreId($storeId);

        $image = $helper->getImageHtmlDeclaration($filename, $asIs);
        $this->getResponse()->setBody($image);
    }

    public function thumbnailAction()
    {
        $file = $this->getRequest()->getParam('file');
        $file = Mage::helper('udropship/wysiwyg_images')->idDecode($file);
        $thumb = $this->getStorage()->resizeOnTheFly($file);
        if ($thumb !== false) {
            $image = Varien_Image_Adapter::factory('GD2');
            $image->open($thumb);
            $image->display();
        } else {
            // todo: genearte some placeholder
        }
    }

    public function getStorage()
    {
        if (!Mage::registry('storage')) {
            $storage = Mage::getModel('udropship/wysiwyg_images_storage');
            Mage::register('storage', $storage);
        }
        return Mage::registry('storage');
    }

    protected function _saveSessionCurrentPath()
    {
        $this->getStorage()
            ->getSession()
            ->setCurrentPath(Mage::helper('udropship/wysiwyg_images')->getCurrentPath());
        return $this;
    }

}
