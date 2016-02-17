<?php

class Neo_AdminFormUpload_Block_Adminhtml_Form_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{

    /**
    * Preparing form
    *
    * @return Mage_Adminhtml_Block_Widget_Form
    */
    protected function _prepareForm()
    {
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
     
        $helper = Mage::helper('neo_adminformupload');
        $fieldset = $form->addFieldset('display', array(
            'legend' => $helper->__('Display Settings'),
            'class' => 'fieldset-wide'
        ));

        $fieldset->addType('image', 'Neo_AdminFormUpload_Block_Adminhtml_Form_Helper_Image');

        $fieldset->addField('title', 'text', array(
          'label'     => $helper->__('Category Folder Name to be Created'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'title',
        ));
     
        $fieldset->addField(
            'images', 
            'image',  //or file
            array( 
                    'name'      => 'images[]', //declare this as array. Otherwise only one image will be uploaded
                    'multiple'  => 'multiple', //declare input as 'multiple'
                    'label'     => $helper->__('Image'),
                    'title'     => $helper->__('Image'),
                    'required'  => true,
                )
         );
     
        /*if (Mage::registry('turnkeye_adminform')) {
            $form->setValues(Mage::registry('turnkeye_adminform')->getData());
        }*/
     
        return parent::_prepareForm();
    }    
}