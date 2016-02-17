<?php
 
class Kitnekaa_Participant_Adminhtml_UsersController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
    	$companyRm = Mage::getResourceModel(
    			'kitnekaa_participant/company'
    	);
    	echo "<pre>";
    	print_r($companyRm->getCompanies());
    	echo "</pre>";
    	// Load the layout handle <adminhtml_example_index>
          $this->loadLayout();
	 
	    // "Inject" into display
	    // THe below example will not actualy show anything since the core/template is empty
	    $this->_addContent($this->getLayout()->createBlock('core/template'));
	 
	    	
		 $this->_setActiveMenu('company')->renderLayout ();
	
    }
 
   
 }