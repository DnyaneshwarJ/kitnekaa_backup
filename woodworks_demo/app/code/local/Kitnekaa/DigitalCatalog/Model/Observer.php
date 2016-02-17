<?php
class Kitnekaa_DigitalCatalog_Model_Observer
{

	public function addCategoryTab(Varien_Event_Observer $observer)
	{
		$tabs = $observer->getEvent()->getTabs();
        $tabs->addTab('features', array(
								            'label'     => Mage::helper('catalog')->__('Digital Catalogs'),
								            //'content' => $tabs->getLayout()->createBlock('digitalcatalog/adminhtml_category_form')->toHtml()
								            'content' => $tabs->getLayout()->createBlock('core/template')->setTemplate('digitalcatalog/form.phtml')->toHtml()
						        ));
	}

	public function categorySave($observer) {
 		
 		$event = $observer->getEvent();
    	$category = $event->getCategory();

    	//Category ID
    	$categoryID = $category->getId();

		//Save PDF File
		if (isset($_FILES['digitalcatalog']['name']) && (file_exists($_FILES['digitalcatalog']['tmp_name']))) {
			
			$uploader = new Varien_File_Uploader('digitalcatalog');
    		//$uploader->setAllowedExtensions(array('jpg','jpeg','gif','png','pdf')); // or pdf or anything
    		$uploader->setAllowedExtensions(array('pdf')); // or pdf or anything

    		$uploader->setAllowRenameFiles(true);

    		$uploader->setFilesDispersion(false);

    		//Digital Catalog Folder Path
    		$digitalCatlogPath = Mage::getBaseDir('media') . DS . 'digital_catalog'. DS;

    		//Create Digital Catalog Folder
    		if(!file_exists($digitalCatlogPath)) {
    			mkdir($digitalCatlogPath, 0755, true);
    		}

    		//Current Category Folder Path
    		$currentCategoryPath = Mage::getBaseDir('media') . DS . 'digital_catalog'. DS . $categoryID. DS;

    		//Create Category ID Folder
    		if(!file_exists($currentCategoryPath)) {
    			mkdir($currentCategoryPath, 0755, false);
    		}

    		$uploader->save($currentCategoryPath, $_FILES['digitalcatalog']['name']);

    		$data['digitalcatalog'] = $_FILES['digitalcatalog']['name'];
		} else {
		    //Get Request Parameters
		    $params = Mage::app()->getFrontController()->getRequest()->getParams();
		    
		    /*Delete Uploaded PDF Files*/
		    if(isset($params['digitalcatalog']) && is_array($params['digitalcatalog'])) {
		    	if(count($params['digitalcatalog'])) {
		    		//List Of PDF Files
		    		$catalogFileList = $params['digitalcatalog'];

		    		foreach($catalogFileList as $fileName) {
		    			//Digital Catalog File Path
				    	$catalogFilePath = Mage::getBaseDir('media') . DS . 'digital_catalog'. DS . $params['id']. DS . $fileName;
				    	unlink($catalogFilePath);
		    		}
		    	}
		    }
		}
	}		
}