<?php
 
class Kitnekaa_Participant_QuoteController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
    	echo "Hello";die();
    	// Load the layout handle <adminhtml_example_index>
          $this->loadLayout();
	 
	    // "Inject" into display
	    // THe below example will not actualy show anything since the core/template is empty
	    $this->_addContent($this->getLayout()->createBlock('core/template'));
	 
	   	
		 $this->_setActiveMenu('company')->renderLayout ();
	
    }
 
   
 }