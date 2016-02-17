<?php
class Kitnekaa_Shoppinglist_Block_Adminhtml_Shoplistform_Form extends Mage_Adminhtml_Block_Widget_Form
{  protected $customer_id;
   protected $company;
   protected function _construct()
    { 
       parent::_construct();
       $this->customer_id = $this->getRequest()->getParam('customer_id');
       $this->company =  Mage::helper('shoppinglist')->findcompany($this->customer_id);
       
        $form = new Varien_Data_Form(array(
            'id'      => 'editForm',
            'name'    => 'editForm',
            'method'  => 'post',
            'enctype' => 'multipart/form-data',
            'action'  => $this->getUrl('*/shoppinglist/saveshoplist'),
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        return $this;
    }
  
  protected function _prepareForm()
  {

        $fieldset = $this->getForm()->addFieldset('my_el_form_fs111', array(
                'legend' => Mage::helper('shoppinglist')->__('shoppinglist Form')
          ));
        $fieldset->addType('extended_text','Kitnekaa_Shoppinglist_Lib_Varien_Data_Form_Element_ExtendedText');
        $element = $fieldset->addField('customer_id', 'hidden', array(
            'label'     => Mage::helper('shoppinglist')->__('new field'),
            'name'      => 'customer_id',
            'value'     =>  $this->customer_id,
            'required'  => false,
        )); 

        $element = $fieldset->addField('company', 'hidden', array(
            'label'     => Mage::helper('shoppinglist')->__('new field'),
            'name'      => 'company',
            'value'     =>  $this->company,
            'required'  => false,
        )); 

        $fieldset->addField('title', 'extended_text', array(
            'label'     => Mage::helper('shoppinglist')->__('Name Of Shopping List'),
            'required'  => true,
            'name'      => 'title',
            'data-model' => 'shoppinglist/shoppinglist',
            'data-field' => "list_name", 
            'data-depend-on' => "company_id",
            'data-depend-value' => $this->company,
             'class' => 'validate-value-exist-depend-on'

                
        ));


       $field  =  $fieldset->addField('submit', 'submit', array(
          'label'     => Mage::helper('shoppinglist')->__(''),
          'value'  => 'Submit',
          
        ));

  }
 



}
