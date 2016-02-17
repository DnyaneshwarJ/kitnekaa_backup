<?php
class Kitnekaa_Participant_Adminhtml_ShoppinglistController extends Mage_Adminhtml_Controller_Action {
	public function indexAction() 
	{
		$participant = (int) $this->getRequest()->getParam('participant', false);
		Mage::register('current_participant', $participant);
		
		// Load the layout handle
		 $shoppinglistBlock = $this->getLayout()
            ->createBlock('kitnekaa_participant/adminhtml_shoppinglist');

        // add the grid container as the only item on this page
        $this->loadLayout()
            	->_addContent($shoppinglistBlock)
        		->_setActiveMenu('company')
        		->renderLayout ();
	}
}