<?php

class Kitnekaa_DigitalCatalog_Block_Adminhtml_Category_Form extends Mage_Adminhtml_Block_Widget_Form {

	protected function _prepareForm() {
		$form = new Varien_Data_Form(
            array(
                'id' => 'edit_form',
                'action' => $this->getUrl('*/*/save', array('id' =>    $this->getRequest()->getParam('id'))),
                'method' => 'post',
                'enctype' => 'multipart/form-data'
            )
        );
     
        $form->setUseContainer(true);
        $this->setForm($form);

        $fieldset = $form->addFieldset('digitalcatalog_category_tab_form', array('legend'=>Mage::helper('catalog')->__('Digital Catalog')));

        $fieldset->addField('digitalcatalog_tab_text','text',array(
				'label' => Mage::helper('catalog')->__('Product Catalog'),
				'class' => '',
				'required' => false,
				'name'=> 'digitalcatalog_tab_text',
				'note' => Mage::helper('catalog')->__('Product Catalog'),
			));

		return parent::_prepareForm();
	}
}