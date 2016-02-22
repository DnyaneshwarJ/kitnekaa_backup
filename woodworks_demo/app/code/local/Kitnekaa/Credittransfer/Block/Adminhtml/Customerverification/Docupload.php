<?php 

class Kitnekaa_Credittransfer_Block_Adminhtml_Customerverification_Docupload
extends Mage_Adminhtml_Block_Widget_Form
 { 

 protected $company;
  protected $financer;
  protected $verifyingname;
  
 protected function _construct()
    { 
        parent::_construct();
       $this->financer = $this->getRequest()->getParam('financer');
       $this->company = $this->getRequest()->getParam('company');
       $this->verifyingname = $this->getRequest()->getParam('verifyingname');
       //die();
        $form = new Varien_Data_Form(array(
            'id'      => 'doc_edit_form',
             'name'  => 'doc_edit_form_ba',
            'method'  => 'post',
            'enctype' => 'multipart/form-data',
            'action'  => $this->getUrl('*/credittransfer/addfile'),
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        return $this;
    }

    protected function _prepareForm()
    {     
     
         $select_array = Mage::helper('credittransfer')->getpagefield($this->company,$this->financer);
         $doc_array = array( 0 => array( 'value' => '', 'label' => 'Selelct'));


        foreach ($select_array as $key => $value){

                          $_menuItems[] = array(
                               'value'     => $value['doc_id'].'_'.$value['has_time_period'],
                                'label'     => $value['doc_name'],
                                
                               );
          }
          $fin_doc_array = array_merge($doc_array,$_menuItems);


       $fieldset = $this->getForm()
            ->addFieldset('my_el_form_fs111', array(
                'legend' => Mage::helper('credittransfer')->__('Upload Form')
            ));

           $element = $fieldset->addField('financer', 'hidden', array(
            'label'     => Mage::helper('credittransfer')->__('new field'),
            'name'      => 'financer',
            'value'     =>  $this->financer,
            'required'  => false,
        )); 

            $element = $fieldset->addField('customer_id', 'hidden', array(
            'label'     => Mage::helper('credittransfer')->__('new field two'),
            'name'      => 'customer_id',
            'value'     =>  $this->company,
            'required'  => false,
        ));  

            $element = $fieldset->addField('verifyingname', 'hidden', array(
            'label'     => Mage::helper('credittransfer')->__('new field two'),
            'name'      => 'verifyingname',
            'value'     =>  $this->verifyingname,
            'required'  => false,
        ));  

         $fieldset->addField('doc_id', 'select', array(
        'name'      => 'doc_id',
        'label'     => Mage::helper('credittransfer')->__('Select Document'),
        'title'     => Mage::helper('credittransfer')->__('Select Document'),
        'required'  => true,
        //'class'     => 'required-entry',
        'values'    => $fin_doc_array,
        'onchange' => "showhidefield();",
  ));

         $fieldset->addField('from_date', 'date', 
          array( 'label' => Mage::helper('credittransfer')->__('From Date'),
          'name' =>  'from_date',
         // 'after_element_html' => '<small>Comments</small>',
           'tabindex' => 1, 
           //'required' => true,
           'image' => $this->getSkinUrl('images/grid-cal.gif'),
          'format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT) ));

             $fieldset->addField('to_date', 'date', 
          array( 'label' => Mage::helper('credittransfer')->__('To Date'),
          'name' =>  'to_date',
         // 'after_element_html' => '<small>Comments</small>',
           'tabindex' => 1, 
           //'required' => true,
           'image' => $this->getSkinUrl('images/grid-cal.gif'),
          'format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT) ));

   $fieldset->addField('file', 'file', array(
          'label'  => Mage::helper('credittransfer')->__('Upload'),
          'value'  => 'Uplaod',
          'name' => 'docfile',
          'required' => true,
          //'after_element_html' => '<small>Comments</small>',
          
          //'class' => 'required-entry required-file',
        ));


       $field  =  $fieldset->addField('submit', 'submit', array(
          'label'     => Mage::helper('credittransfer')->__('Submit'),
          //'required'  => true,
          'value'  => 'Submit',
          //'after_element_html' => '<small>Comments</small>',
          //'tabindex' => 1
        ));


        return parent::_prepareForm();
    }   
}