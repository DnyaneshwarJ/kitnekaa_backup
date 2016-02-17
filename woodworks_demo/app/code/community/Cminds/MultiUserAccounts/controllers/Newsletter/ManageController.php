<?php
/**
 * @author CreativeMindsSolutions
 */
 
require_once 'Mage/Newsletter/controllers/ManageController.php';

class Cminds_MultiUserAccounts_Newsletter_ManageController extends Mage_Newsletter_ManageController {

    /**
     * Change customer password action
     */
    public function saveAction()
    {
        if (Mage::helper('cminds_multiuseraccounts')->hasWritePermission()) {
            return parent::saveAction();
        }
        Mage::getSingleton('customer/session')->addError('You Don\'t have permission for this action');
        return $this->_redirect('*/*/');
    }
}