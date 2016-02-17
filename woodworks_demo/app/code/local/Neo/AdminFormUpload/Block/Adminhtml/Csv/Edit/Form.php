<?php

class Neo_AdminFormUpload_Block_Adminhtml_Csv_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
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
        $fieldset = $form->addFieldset('upload_csv', array(
            'legend' => $helper->__('Upload Csv'),
            'class' => 'fieldset-wide'
        ));

        //$fieldset->addType('image', 'Neo_AdminFormUpload_Block_Adminhtml_Form_Helper_Image');
     
        $fieldset->addField(
            'csv', 
            'file',
            array( 
                    'name'      => 'importcsv',
                    'label'     => $helper->__('Csv'),
                    'title'     => $helper->__('Csv'),
                    'required'  => false,
                )
         );
     
        return parent::_prepareForm();
    }    
}