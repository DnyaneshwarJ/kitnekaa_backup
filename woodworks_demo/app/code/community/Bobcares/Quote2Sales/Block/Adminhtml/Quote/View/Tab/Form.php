<?php
/**
 * Displays the Quote View form
 * @category    Bobcares
 * @package     Bobcares_Quote2Sales
 */

class Bobcares_Quote2Sales_Block_Adminhtml_Quote_View_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
	
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      //$fieldset = $form->addFieldset('edit_form', array('legend'=>Mage::helper('quote2sales')->__('View Quote')));

  /*    $fieldset->addField('key', 'text', array(
          'label'     => Mage::helper('quote2sales')->__('Key'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'key',
      ));
      

      $fieldset->addField('display_value', 'text', array(
          'label'     => Mage::helper('quote2sales')->__('Display Value'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'display_value',
      ));
      
    */  
	  
      if ( Mage::getSingleton('adminhtml/session')->getOsData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getOsData());
          Mage::getSingleton('adminhtml/session')->setOsData(null);
      } elseif ( Mage::registry('quote_data') ) {
          $form->setValues(Mage::registry('quote_data')->getData());
      }
      return parent::_prepareForm();
  }
  
  
  
}