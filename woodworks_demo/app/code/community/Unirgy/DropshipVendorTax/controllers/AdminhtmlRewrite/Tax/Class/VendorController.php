<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml product tax class controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Unirgy_DropshipVendorTax_AdminhtmlRewrite_Tax_Class_VendorController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->_title(Mage::helper('udropship')->__('Sales'))
            ->_title(Mage::helper('udropship')->__('Tax'))
            ->_title(Mage::helper('udropship')->__('Vendor Tax Classes'));

        $this->_initAction()
            ->_addContent(
                $this->getLayout()->createBlock('adminhtml/tax_class')
                    ->setClassType(Unirgy_DropshipVendorTax_Model_Source::TAX_CLASS_TYPE_VENDOR)
            )
            ->renderLayout();
    }

    /**
     * new class action
     *
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * edit class action
     *
     */
    public function editAction()
    {
        $this->_title(Mage::helper('udropship')->__('Sales'))
            ->_title(Mage::helper('udropship')->__('Tax'))
            ->_title(Mage::helper('udropship')->__('Vendor Tax Classes'));

        $classId    = $this->getRequest()->getParam('id');
        $model      = Mage::getModel('tax/class');
        if ($classId) {
            $model->load($classId);
            if (!$model->getId() || $model->getClassType() != Unirgy_DropshipVendorTax_Model_Source::TAX_CLASS_TYPE_VENDOR) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('udropship')->__('This class no longer exists')
                );
                $this->_redirect('*/*/');
                return;
            }
        }

        $this->_title($model->getId() ? $model->getClassName() : Mage::helper('udropship')->__('New Class'));

        $data = Mage::getSingleton('adminhtml/session')->getClassData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        Mage::register('tax_class', $model);

        $this->_initAction()
            ->_addBreadcrumb(
                $classId ? Mage::helper('udropship')->__('Edit Class') :  Mage::helper('udropship')->__('New Class'),
                $classId ?  Mage::helper('udropship')->__('Edit Class') :  Mage::helper('udropship')->__('New Class')
            )
            ->_addContent(
                $this->getLayout()->createBlock('adminhtml/tax_class_edit')
                    ->setData('action', $this->getUrl('*/tax_class/save'))
                    ->setClassType(Unirgy_DropshipVendorTax_Model_Source::TAX_CLASS_TYPE_VENDOR)
            )
            ->renderLayout();
    }

    /**
     * delete class action
     *
     */
    public function deleteAction()
    {
        $classId    = $this->getRequest()->getParam('id');
        $session    = Mage::getSingleton('adminhtml/session');
        $classModel = Mage::getModel('tax/class')
            ->load($classId);

        if (!$classModel->getId() || $classModel->getClassType() != Unirgy_DropshipVendorTax_Model_Source::TAX_CLASS_TYPE_VENDOR) {
            $session->addError(Mage::helper('udropship')->__('This class no longer exists'));
            $this->_redirect('*/*/');
            return;
        }

        $ruleCollection = Mage::getModel('tax/calculation_rule')
            ->getCollection()
            ->setClassTypeFilter(Unirgy_DropshipVendorTax_Model_Source::TAX_CLASS_TYPE_VENDOR, $classId);

        if ($ruleCollection->getSize() > 0) {
            $session->addError(Mage::helper('udropship')->__('You cannot delete this tax class as it is used in Tax Rules. You have to delete the rules it is used in first.'));
            $this->_redirect('*/*/edit/', array('id' => $classId));
            return;
        }

        $vendorCollection = Mage::getModel('udropship/vendor')
            ->getCollection()
            ->addFieldToFilter('vendor_tax_class', $classId);
        $vendorCount = $vendorCollection->getSize();

        if ($vendorCount> 0) {
            $session->addError(Mage::helper('udropship')->__('You cannot delete this tax class as it is used for %d vendors.', $vendorCount));
            $this->_redirect('*/*/edit/', array('id' => $classId));
            return;
        }

        try {
            $classModel->delete();

            $session->addSuccess(Mage::helper('udropship')->__('The tax class has been deleted.'));
            $this->getResponse()->setRedirect($this->getUrl("*/*/"));
            return;
        } catch (Mage_Core_Exception $e) {
            $session->addError($e->getMessage());
        } catch (Exception $e) {
            $session->addException($e, Mage::helper('udropship')->__('An error occurred while deleting this tax class.'));
        }

        $this->_redirect('*/*/edit/', array('id' => $classId));
    }

    /**
     * Initialize action
     *
     * @return Mage_Adminhtml_Controller_Action
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('sales/tax/tax_class_vendor')
            ->_addBreadcrumb(Mage::helper('udropship')->__('Sales'), Mage::helper('udropship')->__('Sales'))
            ->_addBreadcrumb(Mage::helper('udropship')->__('Tax'), Mage::helper('udropship')->__('Tax'))
            ->_addBreadcrumb(Mage::helper('udropship')->__('Manage Vendor Tax Classes'), Mage::helper('udropship')->__('Manage Vendor Tax Classes'))
        ;
        return $this;
    }

    /**
     * Check current user permission on resource and privilege
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/tax/classes_vendor');
    }

}
