<?php

class Unirgy_DropshipShippingClass_Block_Adminhtml_Customer_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();

        $this->setId('udshipclassCustomerForm');
    }

    protected function _prepareForm()
    {
        $model  = Mage::registry('udshipclass_customer');
        $form   = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getData('action'),
            'method'    => 'post'
        ));

        $this->setTitle(Mage::helper('udropship')->__('Customer Ship Class Information'));

        $fieldset   = $form->addFieldset('base_fieldset', array(
            'legend'    => Mage::helper('udropship')->__('Customer Ship Class Information')
        ));

        $fieldset->addField('class_name', 'text',
            array(
                'name'  => 'class_name',
                'label' => Mage::helper('udropship')->__('Class Name'),
                'class' => 'required-entry',
                'value' => $model->getClassName(),
                'required' => true,
            )
        );

        $fieldset->addField('sort_order', 'text', array(
            'name'   => 'sort_order',
            'label'  => Mage::helper('udropship')->__('Sort Order'),
        ));

        $fieldset->addType('shipclass_rows', Mage::getConfig()->getBlockClassName('udshipclass/adminhtml_formField_shipclassRows'));

        $fieldset->addField('rows', 'shipclass_rows',
            array(
                'name'  => 'rows',
                'label' => Mage::helper('udropship')->__('Countries'),
                'class' => 'required-entry',
                'value' => $model->getRows(),
                'required' => true,
            )
        );

        if ($model->getId()) {
            $fieldset->addField('class_id', 'hidden',
                array(
                    'name'      => 'class_id',
                    'value'     => $model->getId(),
                    'no_span'   => true
                )
            );
        }

        $form->setValues($model->getData());
        $form->setAction($this->getUrl('*/udshipclassadmin_customer/save'));
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
