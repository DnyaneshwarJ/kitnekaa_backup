<?php

class Unirgy_DropshipVendorTax_Adminhtml_IndexController extends Mage_Adminhtml_Controller_Action
{
    public function massUpdateVendorTaxClassAction()
    {
        $modelIds = (array)$this->getRequest()->getParam('vendor');
        $vTaxClass = (string)$this->getRequest()->getParam('vendor_tax_class');

        try {
            foreach ($modelIds as $modelId) {
                Mage::getModel('udropship/vendor')->load($modelId)->setVendorTaxClass($vTaxClass)->save();
            }
            $this->_getSession()->addSuccess(
                Mage::helper('udropship')->__('Total of %d record(s) were successfully updated', count($modelIds))
            );
        }
        catch (Mage_Core_Model_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        catch (Exception $e) {
            $this->_getSession()->addException($e, Mage::helper('udropship')->__('There was an error while updating vendor(s) type'));
        }

        $this->_redirect('udropship/adminhtml_vendor/');
    }
}