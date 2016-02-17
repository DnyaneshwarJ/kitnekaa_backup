<?php

class Cminds_MultiUserAccounts_Block_Adminhtml_SubAccount_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{

    /**
     * Initialize form
     *
     * @return Mage_Adminhtml_Block_Customer_Edit_Tab_Account
     */
    protected function _prepareForm()
    {
        $subAccount = Mage::registry('sub_account');
        $data = $subAccount->getData();
        $mode = 'edit';
        if ($subAccount && $subAccount->getId()) {
            $urlParams = array(
                'id' => $this->getRequest()->getParam('id')
            );
        }else{
            $mode = 'new';
            $urlParams = array(
                'parent_customer_id' => $this->getRequest()->getParam('parent_customer_id')
            );
            $data['parent_customer_id'] = $this->getRequest()->getParam('parent_customer_id');
        }

        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/'.$mode.'Post', $urlParams),
            'method' => 'post',
        ));

        $form->setHtmlIdPrefix('_subaccount');
        $form->setFieldNameSuffix('subaccount');

        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend' => Mage::helper('cminds_multiuseraccounts')->__('Account Information')
        ));

        $fieldset->addField('parent_customer_id', 'hidden',
            array(
                'name' => 'parent_customer_id',
                'required' => true,
            )
        );

        // New customer password
        $fieldset->addField('firstname', 'text',
            array(
                'label' => Mage::helper('cminds_multiuseraccounts')->__('First Name'),
                'name' => 'firstname',
                'required' => true,
            )
        );

        $fieldset->addField('lastname', 'text',
            array(
                'label' => Mage::helper('cminds_multiuseraccounts')->__('Last Name'),
                'name' => 'lastname',
                'required' => true
            )
        );

        $fieldset->addField('email', 'text',
            array(
                'label' => Mage::helper('cminds_multiuseraccounts')->__('Email'),
                'name' => 'email',
                'required' => true
            )
        );

        $fieldset->addField('permission', 'select', array(
            'label' => Mage::helper('cminds_multiuseraccounts')->__('Permission'),
            'name' => 'permission',
            'values' => Mage::getModel('cminds_multiuseraccounts/subAccount_permission')->getAllOptions(),
            'required' => true
        ));

        $fieldset->addField('view_all_orders', 'select', array(
            'label' => Mage::helper('cminds_multiuseraccounts')->__('View All Orders'),
            'name' => 'view_all_orders',
            'values' => array(
                array('value' => '0','label' =>Mage::helper('cminds_multiuseraccounts')->__('No')),
                array('value' => '1','label' =>Mage::helper('cminds_multiuseraccounts')->__('Yes')),
            ),
            'required' => true
        ));

        // Add password management fieldset
        $newFieldset = $form->addFieldset(
            'password_fieldset',
            array('legend' => Mage::helper('cminds_multiuseraccounts')->__('Password Management'))
        );
        // New customer password
        $newFieldset->addField('new_password', 'password',
            array(
                'label' => 'new' == $mode ? Mage::helper('cminds_multiuseraccounts')->__('Password') : Mage::helper('cminds_multiuseraccounts')->__('New Password'),
                'name' => 'new_password',
                'class' => 'validate-new-password'
            )
        );

        if('new' == $mode){
            $newFieldset->addField('password_confirmation', 'password',
                array(
                    'label' => Mage::helper('cminds_multiuseraccounts')->__('Confirm Password'),
                    'name' => 'password_confirmation',
                )
            );
        }

//        // Prepare customer confirmation control (only for existing customers)
        $confirmationKey = $subAccount->getConfirmation();
        if ($confirmationKey || $subAccount->isConfirmationRequired()) {
            $fieldset->addField('confirmation', 'select', array(
                'name' => 'confirmation',
                'label' => Mage::helper('cminds_multiuseraccounts')->__('Confirmation'),
                'values' => array(
                    array(
                        'value' => 0,
                        'label' => Mage::helper('cminds_multiuseraccounts')->__('Not Confirmation')
                    ),
                    array(
                        'value' => 1,
                        'label' => Mage::helper('cminds_multiuseraccounts')->__('Confirmation')
                    )
                ),
            ));
        }

        $form->setUseContainer(true);
        $form->setValues($data);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
