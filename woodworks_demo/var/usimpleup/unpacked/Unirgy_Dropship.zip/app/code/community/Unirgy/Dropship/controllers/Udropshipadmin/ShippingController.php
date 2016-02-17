<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    Unirgy_Dropship
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

class Unirgy_Dropship_Udropshipadmin_ShippingController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout();

        $this->_setActiveMenu('sales/udropship');
        $this->_addBreadcrumb(Mage::helper('udropship')->__('Shipping Methods'), Mage::helper('udropship')->__('Shipping Methods'));
        $this->_addContent($this->getLayout()->createBlock('udropship/adminhtml_shipping'));

        $this->renderLayout();
    }

    public function editAction()
    {
        $this->loadLayout();

        $this->_setActiveMenu('sales/udropship');
        $this->_addBreadcrumb(Mage::helper('udropship')->__('Shipping Methods'), Mage::helper('udropship')->__('Shipping Methods'));

        $this->_addContent($this->getLayout()->createBlock('udropship/adminhtml_shipping_edit'))
            ->_addLeft($this->getLayout()->createBlock('udropship/adminhtml_shipping_edit_tabs'));

        $this->renderLayout();
    }

    public function newAction()
    {
        $this->editAction();
    }

    public function saveAction()
    {
        $hlp = Mage::helper('udropship');
        if ( $this->getRequest()->getPost() ) {
            try {
                $r = $this->getRequest();
                $id = $r->getParam('id');
                $new = !$id;

                $postedCount = 0;
                $hasWildcard = false;
                foreach ($r->getParam('system_methods') as $carrier => $method) {
                    if (!empty($method)) {
                        $postedCount++;
                        if ($method == '*') {
                            $hasWildcard = true;
                        }
                    }
                }
                if ($postedCount>1 && $hasWildcard) {
                    Mage::throwException(
                        Mage::helper('udropship')->__('Only one system method could be selected when using "* Any available" wildcard method')
                    );
                }

                $model = Mage::getModel('udropship/shipping')
                    ->setId($id)
                    ->setShippingCode($r->getParam('shipping_code'))
                    ->setShippingTitle($r->getParam('shipping_title'))
                    ->setDaysInTransit($r->getParam('days_in_transit'))
                    ->setWebsiteIds($r->getParam('website_ids'))
                    ->setStoreTitles($r->getParam('store_titles'))
                    ->setPostedSystemMethods($r->getParam('system_methods'));

                $model->save();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('udropship')->__('Shipping Method was successfully saved'));

                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        if(($id = $this->getRequest()->getParam('id')) > 0 ) {
            try {
                $model = Mage::getModel('udropship/shipping');
                /* @var $model Unirgy_Dropship_Model_Shipping */
                $model->setId($id)->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('udropship')->__('Shipping Method was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/udropship/shipping');
    }

    public function gridAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('udropship/adminhtml_shipping_grid')->toHtml()
        );
    }

    /**
     * Export subscribers grid to CSV format
     */
    public function exportCsvAction()
    {
        $fileName   = 'vendors.csv';
        $content    = $this->getLayout()->createBlock('udropship/adminhtml_shipping_grid')
            ->getCsv();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Export subscribers grid to XML format
     */
    public function exportXmlAction()
    {
        $fileName   = 'vendors.xml';
        $content    = $this->getLayout()->createBlock('udropship/adminhtml_shipping_grid')
            ->getXml();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function massDeleteAction()
    {
        $certIds = $this->getRequest()->getParam('shipping');
        if (!is_array($certIds)) {
            $this->_getSession()->addError(Mage::helper('udropship')->__('Please select shipping method(s)'));
        }
        else {
            try {
                $cert = Mage::getSingleton('udropship/shipping');
                foreach ($certIds as $certId) {
                    $cert->setId($certId)->delete();
                }
                $this->_getSession()->addSuccess(
                    Mage::helper('udropship')->__('Total of %d record(s) were successfully deleted', count($certIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
}
