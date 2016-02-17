<?php
/*------------------------------------------------------------------------
 # SM Deal - Version 1.0
 # Copyright (c) 2014 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

class Sm_Deal_Block_Adminhtml_Deal_Edit_Tab_Meta extends Mage_Adminhtml_Block_Widget_Form{

	protected function _prepareForm(){
		$form = new Varien_Data_Form();
		$form->setFieldNameSuffix('deal');
		$this->setForm($form);
		$fieldset = $form->addFieldset('deal_meta_form', array('legend'=>Mage::helper('deal')->__('Meta information')));
		$fieldset->addField('meta_title', 'text', array(
			'label' => Mage::helper('deal')->__('Meta-title'),
			'name'  => 'meta_title',
		));
		$fieldset->addField('meta_description', 'textarea', array(
			'name'  	=> 'meta_description',
			'label' 	=> Mage::helper('deal')->__('Meta-description'),
  		));
  		$fieldset->addField('meta_keywords', 'textarea', array(
			'name'  	=> 'meta_keywords',
			'label' 	=> Mage::helper('deal')->__('Meta-keywords'),
  		));
  		$form->addValues(Mage::registry('current_deal')->getData());
		return parent::_prepareForm();
	}
}