<?php
/**
 * @author CreativeMindsSolutions
 */
 
require_once 'Mage/Customer/controllers/AddressController.php';


class Cminds_MultiUserAccounts_Customer_Address_AddressController extends Mage_Customer_AddressController {

    /**
     * Change customer password action
     */
    public function formPostAction()
    {
        if (Mage::helper('cminds_multiuseraccounts')->hasWritePermission()) {
            return parent::formPostAction();
        }
        Mage::getSingleton('customer/session')->addError('You Don\'t have permission for this action');

        if (count($this->_getSession()->getCustomer()->getAddresses())) {
            return $this->_redirect('*/*/');
        }else{
            return $this->_redirect('customer/account/');
        }
    }

    public function newAction()
    {
        if (Mage::helper('cminds_multiuseraccounts')->hasWritePermission()) {
            return parent::newAction();
        }
        Mage::getSingleton('customer/session')->addError('You Don\'t have permission for this action');

        if (count($this->_getSession()->getCustomer()->getAddresses())) {
            return $this->_redirect('*/*/');
        }else{
            return $this->_redirect('customer/account/');
        }
    }

}