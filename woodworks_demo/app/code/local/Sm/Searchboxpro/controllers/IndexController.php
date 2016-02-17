<?php
/*------------------------------------------------------------------------
 # SM Search Box Pro - Version 1.0
 # Copyright (c) 2013 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

class Sm_Searchboxpro_IndexController extends Mage_Core_Controller_Front_Action
{
    public function IndexAction()
    {
		$this->loadLayout();     
		$this->renderLayout();
    }
	
	public function ajaxAction() {
		//Zend_Debug::dump(Mage::app()->getRequest()->getParams());
		//echo"test";die;
		$layout = Mage::getSingleton('core/layout');
		$block = $layout->createBlock('searchboxpro/list');
		
		header('content-type: text/javascript');
		
		echo '{"htm":' . json_encode($block->toHtml()) .'}';
		//echo $block->toHtml();
		die();			
		$this->renderLayout();
    }
}
