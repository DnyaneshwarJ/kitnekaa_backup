<?php
/*------------------------------------------------------------------------
 # SM Tab Listing - Version 1.1
 # Copyright (c) 2013 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

class Sm_Tablisting_IndexController extends Mage_Core_Controller_Front_Action{

    public function IndexAction() {
	  $this->loadLayout();
      $this->renderLayout();
    }
	
	public function ajaxAction() {
		$layout   = Mage::getSingleton('core/layout');
		$block    = $layout->createBlock('tablisting/list');
		header('content-type: text/javascript');
		echo '{"items_markup":' . json_encode($block->toHtml()) .'}';	
		$this->renderLayout();
    }
	
}