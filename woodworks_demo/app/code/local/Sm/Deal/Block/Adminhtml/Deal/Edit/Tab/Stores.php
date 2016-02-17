<?php
/*------------------------------------------------------------------------
 # SM Deal - Version 1.0
 # Copyright (c) 2014 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/ 
 
class Sm_Deal_Block_Adminhtml_Deal_Edit_Tab_Stores extends Mage_Adminhtml_Block_Widget_Form{

	protected function _prepareForm(){
		$form = new Varien_Data_Form();
		$form->setFieldNameSuffix('deal');
		$this->setForm($form);
		$fieldset = $form->addFieldset('deal_stores_form', array('legend'=>Mage::helper('deal')->__('Store views')));
		$field = $fieldset->addField('store_id', 'multiselect', array(
			'name'  => 'stores[]',
			'label' => Mage::helper('deal')->__('Store Views'),
			'title' => Mage::helper('deal')->__('Store Views'),
			'required'  => true,
			'values'=> Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
		));
		$renderer = $this->getLayout()->createBlock('adminhtml/store_switcher_form_renderer_fieldset_element');
		$field->setRenderer($renderer);
  		$form->addValues(Mage::registry('current_deal')->getData());
		return parent::_prepareForm();
	}
}