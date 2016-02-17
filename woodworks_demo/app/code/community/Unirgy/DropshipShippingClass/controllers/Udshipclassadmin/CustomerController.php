<?php

class Unirgy_DropshipShippingClass_Udshipclassadmin_CustomerController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->_title(Mage::helper('udropship')->__('Sales'))
             ->_title(Mage::helper('udropship')->__('Dropship'))
             ->_title(Mage::helper('udropship')->__('Customer Ship Classes'));

        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('udshipclass/adminhtml_customer'))
            ->renderLayout();
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $this->_title(Mage::helper('udropship')->__('Sales'))
             ->_title(Mage::helper('udropship')->__('Dropship'))
             ->_title(Mage::helper('udropship')->__('Customer Ship Classes'));

        $classId    = $this->getRequest()->getParam('id');
        $model      = Mage::getModel('udshipclass/customer');
        if ($classId) {
            $model->load($classId);
            if (!$model->getId()) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('udropship')->__('This class no longer exists'));
                $this->_redirect('*/*/');
                return;
            }
        }

        $this->_title($model->getId() ? Mage::helper('udropship')->__('"%s" Customer Ship Class', $model->getClassName()) : Mage::helper('udropship')->__('New Customer Ship Class'));

        $data = Mage::getSingleton('adminhtml/session')->getUdshipclassCustomerData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        Mage::register('udshipclass_customer', $model);

        $this->_initAction()
            ->_addBreadcrumb($classId ? Mage::helper('udropship')->__('Edit Class') :  Mage::helper('udropship')->__('New Class'), $classId ?  Mage::helper('udropship')->__('Edit Customer Ship Class') :  Mage::helper('udropship')->__('New Class'))
            ->_addContent($this->getLayout()->createBlock('udshipclass/adminhtml_customer_edit')->setData('action', $this->getUrl('*/udshipclassadmin_customer/save')))
            ->renderLayout();
    }

    public function saveAction()
    {
        if ($postData = $this->getRequest()->getPost()) {
            $model = Mage::getModel('udshipclass/customer')
                ->setData($postData);

            try {
                $model->save();
                $classId    = $model->getId();
                $classType  = $model->getClassType();
                $classUrl   = '*/udshipclassadmin_customer';

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('udropship')->__('The customer ship class has been saved.'));
                $this->_redirect($classUrl);

                return ;
            }
            catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setUdshipclassCustomerData($postData);
                $this->_redirectReferer();
            }
            catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('udropship')->__('An error occurred while saving this customer ship class.'));
                Mage::getSingleton('adminhtml/session')->setUdshipclassCustomerData($postData);
                $this->_redirectReferer();
            }

            $this->_redirectReferer();
            return;
        }
        $this->getResponse()->setRedirect($this->getUrl('*/udshipclassadmin_customer'));
    }

    public function deleteAction()
    {
        $classId    = $this->getRequest()->getParam('id');
        $classModel = Mage::getModel('udshipclass/customer')
            ->load($classId);

        if (!$classModel->getId()) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('udropship')->__('This class no longer exists'));
            $this->_redirect('*/*/');
            return;
        }

        try {
            $classModel->delete();

            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('udropship')->__('The ship class has been deleted.'));
            $this->getResponse()->setRedirect($this->getUrl("*/*/"));
            return;
        }
        catch (Mage_Core_Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('udropship')->__('An error occurred while deleting this ship class.'));
        }

        $this->_redirectReferer();
    }

    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('sales/udropship/udshipclass_customer')
            ->_addBreadcrumb(Mage::helper('udropship')->__('Sales'), Mage::helper('udropship')->__('Sales'))
            ->_addBreadcrumb(Mage::helper('udropship')->__('Dropship'), Mage::helper('udropship')->__('Dropship'))
            ->_addBreadcrumb(Mage::helper('udropship')->__('Customer Ship Classes'), Mage::helper('udropship')->__('Customer Ship Classes'))
        ;
        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/udropship/udshipclass_customer');
    }

}
