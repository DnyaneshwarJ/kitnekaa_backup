<?php
class Kitnekaa_Shoppinglist_Block_Adminhtml_Shownonexistform_Form extends Mage_Adminhtml_Block_Widget_Form
{  protected $customer_id;
   protected $company;
   protected $list_id;
   protected $prduct_id = 0;
   protected function _construct()
    { 
       parent::_construct();
       $this->customer_id = $this->getRequest()->getParam('customer_id');
       $this->list_id = $this->getRequest()->getParam('list_id');
       $this->company =  Mage::helper('shoppinglist')->findcompany($this->customer_id);
       $new_prod = $this->getRequest()->getParam('product_id');
      if(isset($new_prod)){
         $this->product_id = $new_prod;


      }
        $form = new Varien_Data_Form(array(
            'id'      => 'editForm',
            'name'    => 'editForm',
            'method'  => 'post',
            'enctype' => 'multipart/form-data',
            'action'  => $this->getUrl('*/shoppinglist/savenonexistprod'),
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

        $element = $fieldset->addField('customer_id', 'hidden', array(
            'label'     => Mage::helper('shoppinglist')->__('new field'),
            'name'      => 'customer_id',
            'value'     =>  $this->customer_id,
            'required'  => false,
        )); 
        $element = $fieldset->addField('list_id', 'hidden', array(
            'label'     => Mage::helper('shoppinglist')->__('list_id'),
            'name'      => 'list_id',
            'value'     =>  $this->list_id,
            'required'  => false,
        )); 
        $element = $fieldset->addField('company', 'hidden', array(
            'label'     => Mage::helper('shoppinglist')->__('new field'),
            'name'      => 'company',
            'value'     =>  $this->company,
            'required'  => false,
        )); 

        if($this->product_id <= 0 )
        {
                $fieldset->addField('prodname', 'text', array(
                    'label'     => Mage::helper('shoppinglist')->__('Name Of Product'),
                    'required'  => true,
                    'name'      => 'prodname',
                ));

                $fieldset->addField('description', 'text', array(
                    'label'     => Mage::helper('shoppinglist')->__('Description'),
                    'required'  => true,
                    'name'      => 'description',
                ));


                $fieldset->addField('sku', 'text', array(
                    'label'     => Mage::helper('shoppinglist')->__('Sku'),
                    'required'  => true,
                    'name'      => 'sku',
                ));

                 $fieldset->addField('Upload', 'button', array(
                  'label'  => Mage::helper('shoppinglist')->__('Upload'),
                  'value'  => 'Uplaod',
                  'name' => 'prodfile[]',
                  'class' => 'add',
                  'container_id'  => 'some-row-id'
                ));

               $field  =  $fieldset->addField('submit', 'submit', array(
                  'label'     => Mage::helper('shoppinglist')->__('Submit'),
                  'value'  => 'Submit',
                  
                ));
     }else{
                $list_item = Mage::getModel('shoppinglist/shoppinglistitems')->load($this->product_id);

                $element = $fieldset->addField('product_id', 'hidden', array(
                    'label'     => Mage::helper('shoppinglist')->__('product_id'),
                    'name'      => 'product_id',
                    'value'     =>  $this->product_id,
                    'required'  => false,
                )); 
                $fieldset->addField('prodname', 'text', array(
                    'label'     => Mage::helper('shoppinglist')->__('Name Of Product'),
                    'required'  => true,
                    'name'      => 'prodname',
                    'value'     => $list_item->getItemName()
                ));

                $fieldset->addField('description', 'text', array(
                    'label'     => Mage::helper('shoppinglist')->__('Description'),
                    'required'  => true,
                    'name'      => 'description',
                    'value'     => $list_item->getDescription()
                ));


                $fieldset->addField('sku', 'text', array(
                    'label'     => Mage::helper('shoppinglist')->__('Sku'),
                    'required'  => true,
                    'name'      => 'sku',
                    'value'     => $list_item->getSku()
                ));

                 $fieldset->addField('Upload', 'button', array(
                  'label'  => Mage::helper('shoppinglist')->__('Upload'),
                  'value'  => 'Uplaod',
                  'name' => 'prodfile[]',
                  'class' => 'add',
                  'container_id'  => 'some-row-id'
                ));

               $field  =  $fieldset->addField('submit', 'submit', array(
                  'label'     => Mage::helper('shoppinglist')->__('Submit'),
                  'value'  => 'Submit',
                  
                ));  



           }


  }
 



}
