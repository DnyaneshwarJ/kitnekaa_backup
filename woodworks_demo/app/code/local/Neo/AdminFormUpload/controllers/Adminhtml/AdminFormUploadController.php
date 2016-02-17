<?php

class Neo_AdminFormUpload_Adminhtml_AdminFormUploadController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
    {
        return true;
    }

    /**
     * View form action
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('adminformupload/form');
        $this->_addContent($this->getLayout()->createBlock('neo_adminformupload/adminhtml_form_edit'));
        $this->renderLayout();
    }

    /**
     * Save Action
     * upload the images to the server
     *
     * @return void
     */
    public function saveAction()
    {   
        if($data = $this->getRequest()->getPost())
        {
            if(isset($_FILES['images']['name'])) {
                $uploaded_images = array();
                $images_count = count($_FILES['images']['name']);
                $uploaded_images_count = 0;
                $path = Mage::getBaseDir('media') . DS .'import/'.$data['title'];
                if(!is_dir($path)) {
                    mkdir($path);
                }

                foreach ($_FILES['images']['name'] as $key => $image) {
                    try {
                        $uploader = new Varien_File_Uploader(
                            array(
                                'name' => $_FILES['images']['name'][$key],
                                'type' => $_FILES['images']['type'][$key],
                                'tmp_name' => $_FILES['images']['tmp_name'][$key],
                                'error' => $_FILES['images']['error'][$key],
                                'size' => $_FILES['images']['size'][$key]
                            )
                        );
                        $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png')); // or pdf or anything
                        $uploader->setAllowRenameFiles(false);
                        $uploader->setFilesDispersion(false);
                        $uploaded = $uploader->save($path, $_FILES['images']['name'][$key]);
                        if($uploaded['error'] == 0){
                            $uploaded_images_count = $uploaded_images_count + 1;
                            $uploaded_images[$key] = $_FILES['images']['name'][$key]." has been successfully uploaded";
                        }
                        $data['images'] = $_FILES['images']['name'][$key];
                        //$this->_redirect('neoadminformupload/adminhtml_adminformupload/index');
                    }catch(Exception $e) {
                        Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                        //$this->_redirect('neoadminformupload/adminhtml_adminformupload/index');
                    }
                }
                
                if($images_count == $uploaded_images_count):
                    Mage::getSingleton('adminhtml/session')->addSuccess("Total ".$uploaded_images_count." images uploded out of ".$images_count." Images has been uploaded");
                    foreach ($uploaded_images as $uploaded_image){
                        Mage::getSingleton('adminhtml/session')->addSuccess($uploaded_image);
                    }
                    //$this->_redirect('neoadminformupload/adminhtml_adminformupload/index');
                else:
                    Mage::getSingleton('adminhtml/session')->addError("Total ".$uploaded_images_count." images uploded out of ".$images_count." Images has been uploaded");
                    foreach ($uploaded_images as $uploaded_image){
                        Mage::getSingleton('adminhtml/session')->addError($uploaded_image);
                    }
                endif;
            }
        }
        $this->_redirect('neoadminformupload/adminhtml_adminformupload/index');
    }
}