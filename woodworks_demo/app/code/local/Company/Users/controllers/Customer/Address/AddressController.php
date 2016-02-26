<?php
/**
 * @author CreativeMindsSolutions
 */
 
require_once 'Cminds/MultiUserAccounts/controllers/Customer/Address/AddressController.php';


class Company_Users_Customer_Address_AddressController extends Cminds_MultiUserAccounts_Customer_Address_AddressController
{
    /**
     * Change customer password action
     */
    public function formPostAction()
    {
        if (Mage::helper('cminds_multiuseraccounts')->hasWritePermission()) {
            $popup= $this->getRequest()->getParam('popup');
            if($popup){
                return $this->newFormPost();
            }else
            {
                return parent::formPostAction();
            }
        }
        Mage::getSingleton('customer/session')->addError('You Don\'t have permission for this action');

        if (count($this->_getSession()->getCustomer()->getAddresses())) {
            return $this->_redirect('*/*/');
        }else{
            return $this->_redirect('customer/account/');
        }
    }

    protected function newFormPost()
    {
        if (!$this->_validateFormKey()) {
            return $this->_redirect('*/*/');
        }
        // Save data
        if ($this->getRequest()->isPost()) {
            $customer = $this->_getSession()->getCustomer();
            /* @var $address Mage_Customer_Model_Address */
            $address  = Mage::getModel('customer/address');
            $addressId = $this->getRequest()->getParam('id');
            if ($addressId) {
                $existsAddress = $customer->getAddressById($addressId);
                if ($existsAddress->getId() && $existsAddress->getCustomerId() == $customer->getId()) {
                    $address->setId($existsAddress->getId());
                }
            }

            $errors = array();

            /* @var $addressForm Mage_Customer_Model_Form */
            $addressForm = Mage::getModel('customer/form');
            $addressForm->setFormCode('customer_address_edit')
                ->setEntity($address);
            $addressData    = $addressForm->extractData($this->getRequest());
            $addressErrors  = $addressForm->validateData($addressData);
            if ($addressErrors !== true) {
                $errors = $addressErrors;
            }

            try {
                $addressForm->compactData($addressData);
                $address->setCustomerId($customer->getId())
                    ->setIsDefaultBilling($this->getRequest()->getParam('default_billing', false))
                    ->setIsDefaultShipping($this->getRequest()->getParam('default_shipping', false));

                $addressErrors = $address->validate();
                if ($addressErrors !== true) {
                    $errors = array_merge($errors, $addressErrors);
                }

                if (count($errors) === 0) {
                    $addressId=$address->save()->getId();
                    $this->_getSession()->setLastAddressId($addressId);
                    $this->_getSession()->addSuccess($this->__('The address has been saved.'));
                    $this->_redirectSuccess(Mage::helper('core/http')->getHttpReferer());
                    return;
                } else {
                    $this->_getSession()->setAddressFormData($this->getRequest()->getPost());
                    foreach ($errors as $errorMessage) {
                        $this->_getSession()->addError($errorMessage);
                    }
                }
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->setAddressFormData($this->getRequest()->getPost())
                    ->addException($e, $e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->setAddressFormData($this->getRequest()->getPost())
                    ->addException($e, $this->__('Cannot save address.'));
            }
        }

        return $this->_redirectError(Mage::getUrl('*/*/edit', array('id' => $address->getId())));
    }
}