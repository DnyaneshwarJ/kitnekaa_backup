<?php
/*------------------------------------------------------------------------
 # SM Deal - Version 1.0
 # Copyright (c) 2014 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

class Sm_Deal_Block_Adminhtml_Deal_Edit extends Mage_Adminhtml_Block_Widget_Form_Container{

	public function __construct(){
		parent::__construct();
		$this->_blockGroup = 'deal';
		$this->_controller = 'adminhtml_deal';
		$this->_updateButton('delete', 'label', Mage::helper('deal')->__('Delete'));
		$this->_removeButton('save');
		$this->_addButton('save', array(
			'label'		=> Mage::helper('deal')->__('Save'),
			'onclick'	=> 'saveEdit()',
			'class'		=> 'save',
		), -50);
		$this->_addButton('saveandcontinue', array(
			'label'		=> Mage::helper('deal')->__('Save And Continue Edit'),
			'onclick'	=> 'saveAndContinueEdit()',
			'class'		=> 'save',
		), -100);
		$this->_formScripts[] = "
			function endAfterStart(start,end){
				return new Date(start.split('/').reverse().join('/')) <=
				new Date(end.split('/').reverse().join('/'));
			}
			function Checkdate(start,end){
			if(start != '' && end != '') {
			var parContainer = document.getElementById('note_end_date');
			var msgContainer1 = document.getElementById('datevalidation');
			if(msgContainer1)
					{
						parContainer.removeChild(msgContainer1);
					}
				if(endAfterStart(start,end) == true)
				{
				}
				else{
					var msgContainer = document.createElement('div');
					msgContainer.setAttribute('id', 'datevalidation');  //set id
					msgContainer.setAttribute('class', 'validation-advice');  //set id
					msgContainer.style.color='#D40707';
					msgContainer.style.fontWeight='bold';
					msgContainer.style.fontSize ='11.4px';
					msgContainer.style.marginLeft ='-5px';
					msgContainer.innerHTML = 'End Date Must be greater than Start Date';
					parContainer.insertBefore(msgContainer,parContainer.childNodes[0]);
				}
				}
			}
			
			function showproductmsg()
			{
					var parContainer = document.getElementById('deal_tabs_products_content');
					var msgContainer1 = document.getElementById('xyz');
					if(msgContainer1)
					{
						parContainer.removeChild(msgContainer1);
					}
					var msgContainer = document.createElement('div');
					msgContainer.setAttribute('id', 'xyz');  //set id
					msgContainer.style.color='red';
					msgContainer.innerHTML = 'Please Select At least One Product';
					parContainer.insertBefore(msgContainer,parContainer.childNodes[0]);
					
					document.getElementById('deal_tabs_form_deal').className = 'tab-item-link';
					document.getElementById('deal_tabs_form_store_deal').className = 'tab-item-link';
					document.getElementById('deal_tabs_products').className = 'tab-item-link error active';
					
					document.getElementById('deal_tabs_form_deal_content').style.display = 'none';
					document.getElementById('deal_tabs_form_store_deal_content').style.display = 'none';
					document.getElementById('deal_tabs_products_content').style.display = 'block';
					return false;
			}
			
			function showProductsTab(method) { 
			
				var cboxes = document.getElementsByName('items[]');
				var len = cboxes.length;
				for (var i=0; i<len; i++) 
				{ 
					if(cboxes[i].checked)
						{ 
							if(method == 1)
							{
							 editForm.submit($('edit_form').action+'back/edit/');
							 return true;
							}
							else
							{ 
								editForm.submit($('edit_form').action);
								return true;
							
							}
						}
				}
				setTimeout(showproductmsg, 1000);	
				return false;
			}
			function saveAndContinueEdit(){
				showProductsTab(1);
			}
			function saveEdit(){
				showProductsTab(0);
			}
		";
	}

	public function getHeaderText(){
		if( Mage::registry('deal_data') && Mage::registry('deal_data')->getId() ) {
			return Mage::helper('deal')->__("Edit Deal '%s'", $this->htmlEscape(Mage::registry('deal_data')->getName()));
		} 
		else {
			return Mage::helper('deal')->__('Add Deal');
		}
	}
}