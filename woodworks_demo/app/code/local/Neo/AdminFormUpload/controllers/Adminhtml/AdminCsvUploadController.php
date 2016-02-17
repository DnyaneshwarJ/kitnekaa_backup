<?php

class Neo_AdminFormUpload_Adminhtml_AdminCsvUploadController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
    {
        return true;
    }

    public function indexAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('adminformupload/csv');
        $this->_addContent($this->getLayout()->createBlock('neo_adminformupload/adminhtml_csv_edit'));
        $this->renderLayout();
    }

    public function saveAction()
    {
        if($data = $this->getRequest()->getPost())
        {
            $path = Mage::getBaseDir('var') . DS .'import/';
            if(isset($_FILES['importcsv']['name'])) {
                $csv_count = count($_FILES['importcsv']['name']);
                $uploaded_csv_count = 0;
                try {
                    $uploader = new Varien_File_Uploader(
                        array(
                            'name' => $_FILES['importcsv']['name'],
                            'type' => $_FILES['importcsv']['type'],
                            'tmp_name' => $_FILES['importcsv']['tmp_name'],
                            'error' => $_FILES['importcsv']['error'],
                            'size' => $_FILES['importcsv']['size']
                        )
                    );

                    $uploader->setAllowedExtensions(array('csv')); // or pdf or anything
                    $uploader->setAllowRenameFiles(false);
                    $uploader->setFilesDispersion(false);

                    if(!is_dir($path)) {
                        mkdir($path);
                    }

                    $uploaded = $uploader->save($path, $_FILES['importcsv']['name']);
                    if($uploaded['error'] == 0){
                        $uploaded_csv_count = $uploaded_csv_count + 1;
                        $uploaded_csv = $_FILES['importcsv']['name']." has been successfully uploaded";
                    }
                    $data['csv'] = $_FILES['importcsv']['name'];
                    $this->_redirect('neoadminformupload/adminhtml_admincsvupload/index');
                }catch(Exception $e) {
                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                    $this->_redirect('neoadminformupload/adminhtml_admincsvupload/index');
                }


                if($csv_count == $uploaded_csv_count):
                    Mage::getSingleton('adminhtml/session')->addSuccess("Csv File has been uploaded to ".$path);
                else:
                    Mage::getSingleton('adminhtml/session')->addError("Csv File has not been uploaded to ".$path);
                endif;
            }else{
                Mage::getSingleton('adminhtml/session')->addError("Please input csv file");
                $this->_redirect('neoadminformupload/adminhtml_admincsvupload/index');
            }
        }
    }

    /**
     * registry form object
     */
    /*protected function _registryObject()
    {
//        Mage::register('turnkeye_adminform', Mage::getModel('turnkeye_adminform/form'));
    }*/

}