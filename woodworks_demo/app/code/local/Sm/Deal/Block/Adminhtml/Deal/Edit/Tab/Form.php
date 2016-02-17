<?php
/*------------------------------------------------------------------------
 # SM Deal - Version 1.0
 # Copyright (c) 2014 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

class Sm_Deal_Block_Adminhtml_Deal_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form{	

	protected function _prepareForm(){
		$form = new Varien_Data_Form();
		$form->setHtmlIdPrefix('deal_');
		$form->setFieldNameSuffix('deal');
		$this->setForm($form);
		$fieldset = $form->addFieldset('deal_form', array('legend'=>Mage::helper('deal')->__('Deal Information')));

		$fieldset->addField('name', 'text', array(
			'label' => Mage::helper('deal')->__('Deal Name'),
			'name'  => 'name',
			'required'  => true,
			'class' => 'required-entry',
			'onchange' => "document.getElementById('deal_url_key').value  = (document.getElementById('deal_name').value).replace(/[^a-z0-9\s]/gi, '').replace(/[_\s]/g, '').toLowerCase();",
		));
		$dateFormatIso = Mage::app()->getLocale()->getDateFormat(
			Mage_Core_Model_Locale::FORMAT_TYPE_SHORT
		);

		$fieldset->addField('start_date', 'date', array(
			'label' => Mage::helper('deal')->__('Start Date'),
			'name'  => 'start_date',
			'note'	=> $this->__('Deal Start Date'),
			'required'  => true,
			'class' => 'required-entry',
			'image'	 => $this->getSkinUrl('images/grid-cal.gif'),
			'format'	=> $dateFormatIso,
			'onchange' => "Checkdate(document.getElementById('deal_start_date').value,document.getElementById('deal_end_date').value)"
		));
		$dateFormatIso = Mage::app()->getLocale()->getDateFormat(
			Mage_Core_Model_Locale::FORMAT_TYPE_SHORT
		);

		$fieldset->addField('end_date', 'date', array(
			'label' => Mage::helper('deal')->__('End Date'),
			'name'  => 'end_date',
			'note'	=> $this->__('Deal Expiry Date'),
			'required'  => true,
			'class' => 'required-entry',
			'image'	 => $this->getSkinUrl('images/grid-cal.gif'),
			'format'	=> $dateFormatIso,
			'onchange' => "Checkdate(document.getElementById('deal_start_date').value,document.getElementById('deal_end_date').value)"
		));
		$fieldset->addField('url_key', 'text', array(
			'label' => Mage::helper('deal')->__('Url key'),
			'name'  => 'url_key',
			'required'  => true,
			'class' => 'required-entry',
			'note'	=> Mage::helper('deal')->__('Relative to Website Base URL')
		));
		$fieldset->addField('status', 'select', array(
			'label' => Mage::helper('deal')->__('Status'),
			'name'  => 'status',
			'values'=> array(
				array(
					'value' => '1',
					'label' => Mage::helper('deal')->__('Enabled'),
				),
				array(
					'value' => '0',
					'label' => Mage::helper('deal')->__('Disabled'),
				),
			),
			'value'     => 1 //Default value
		));
		$fieldset->addField('in_rss', 'select', array(
			'label' => Mage::helper('deal')->__('Show in rss'),
			'name'  => 'in_rss',
			'values'=> array(
				array(
					'value' => 1,
					'label' => Mage::helper('deal')->__('Yes'),
				),
				array(
					'value' => 0,
					'label' => Mage::helper('deal')->__('No'),
				),
			),
		));
		if (Mage::app()->isSingleStoreMode()){
			$fieldset->addField('store_id', 'hidden', array(
                'name'      => 'stores[]',
                'value'     => Mage::app()->getStore(true)->getId()
            ));
            Mage::registry('current_deal')->setStoreId(Mage::app()->getStore(true)->getId());
		}
		if (Mage::getSingleton('adminhtml/session')->getDealData()){
			$form->setValues(Mage::getSingleton('adminhtml/session')->getDealData());
			Mage::getSingleton('adminhtml/session')->setDealData(null);
		}
		elseif (Mage::registry('current_deal')){
			$form->setValues(Mage::registry('current_deal')->getData());
		}
		return parent::_prepareForm();
	}
}