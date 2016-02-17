<?php
/*------------------------------------------------------------------------
 # SM Deal - Version 1.0
 # Copyright (c) 2014 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

class Sm_Deal_Adminhtml_Deal_Deal_WidgetController extends Mage_Adminhtml_Controller_Action{

	public function chooserAction(){
		$uniqId = $this->getRequest()->getParam('uniq_id');
		$grid = $this->getLayout()->createBlock('deal/adminhtml_deal_widget_chooser', '', array(
			'id' => $uniqId,
		));
		$this->getResponse()->setBody($grid->toHtml());
	}
}