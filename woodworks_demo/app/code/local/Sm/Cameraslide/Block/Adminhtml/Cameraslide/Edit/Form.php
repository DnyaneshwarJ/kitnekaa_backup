<?php
	/**
	* 
	*/
	class Sm_Cameraslide_Block_Adminhtml_Cameraslide_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
	{
		
		protected function _prepareForm()
		{
			$form = new Varien_Data_Form(array(
				'id' 		=> 'cameraslide_form',
				'action' 	=> $this->getUrl('*/*/save', $arrayName = array('id' => $this->getRequest()->getParam('id'))),
				'method'	=> 'post',
				'enctype'	=> 'multipart/form-data'
			));

			$form->setUseContainer(true);
			$this->setForm($form);
			return parent::_prepareForm();
		}
	}
?>