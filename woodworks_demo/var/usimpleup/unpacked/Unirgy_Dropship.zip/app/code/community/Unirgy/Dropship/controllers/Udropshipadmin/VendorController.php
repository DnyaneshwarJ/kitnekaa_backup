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

class Unirgy_Dropship_Udropshipadmin_VendorController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
$timer = microtime(true);
        try {
            Unirgy_Dropship_Helper_Protected::validateLicense('Unirgy_Dropship');
        } catch (Exception $e) {
//print_r($e);
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
//echo microtime(true)-$timer; exit;

        $this->loadLayout();

        $hlp = Mage::helper('udropship');

        $this->_setActiveMenu('sales/udropship');
        $this->_addBreadcrumb(Mage::helper('udropship')->__('Vendors'), Mage::helper('udropship')->__('Vendors'));
        $this->_addContent($this->getLayout()->createBlock('udropship/adminhtml_vendor'));

        $this->renderLayout();
    }

    public function editAction()
    {
        $this->loadLayout();

        if (Mage::helper('udropship')->isWysiwygAllowed()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }

        $this->_setActiveMenu('sales/udropship');
        $this->_addBreadcrumb(Mage::helper('udropship')->__('Vendors'), Mage::helper('udropship')->__('Vendors'));

        $this->_addContent($this->getLayout()->createBlock('udropship/adminhtml_vendor_edit'))
            ->_addLeft($this->getLayout()->createBlock('udropship/adminhtml_vendor_edit_tabs'));

        $this->renderLayout();
    }

    public function newAction()
    {
        $this->editAction();
    }

    public function saveAction()
    {
        if ( $this->getRequest()->getPost() ) {
            $r = $this->getRequest();
            $hlp = Mage::helper('udropship');
            try {
                $id = $r->getParam('id');
                $new = !$id;
                $data = $r->getParams();
                $data['vendor_id'] = $id;
                $data['status'] = $data['status1'];

                $model = Mage::getModel('udropship/vendor');
                if ($id) {
                    $model->load($id);
                }
                $hlp->processPostMultiselects($data);
                $model->addData($data);

                $shipping = array();
                if ($r->getParam('vendor_shipping')) {
                    $shipping = Zend_Json::decode($r->getParam('vendor_shipping'));
                }
                $model->setPostedShipping($shipping);

                $products = array();
                if ($r->getParam('vendor_products')) {
                    $products = Zend_Json::decode($r->getParam('vendor_products'));
                }
                $model->setPostedProducts($products);

                Mage::getSingleton('adminhtml/session')->setData('uvendor_edit_data', $model->getData());
                $model->save();
                Mage::getSingleton('adminhtml/session')->unsetData('uvendor_edit_data');

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('udropship')->__('Vendor was successfully saved'));

                $nonSavedMethodIds = array_diff(array_keys($shipping), array_keys($model->getNonCachedShippingMethods()));

                if (!empty($nonSavedMethodIds)) {
                    $shippingMethods = $hlp->getShippingMethods();
                    $nonSavedMethods = array();
                    foreach ($nonSavedMethodIds as $id) {
                        if (($sItem = $shippingMethods->getItemById($id))) {
                            $nonSavedMethods[$id] = $sItem->getShippingTitle();
                        }
                    }
                    if (!empty($nonSavedMethods)) {
                        Mage::getSingleton('adminhtml/session')->addNotice(Mage::helper('udropship')->__('This shipping methods were not saved: %s. Try to use overrides.', implode(', ', $nonSavedMethods)));
                    }
                    $this->_redirect('*/*/edit', array('id' => $model->getId(), 'tab'=>'shipping_section'));
                } else {
                    if ($r->getParam('save_continue')) {
                        $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    } else {
                        $this->_redirect('*/*/');
                    }
                }
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                if ($r->getParam('reg_id')) {
                    $this->_redirect('adminhtml/umicrositeadmin_registration/edit', array('reg_id'=>$r->getParam('reg_id')));
                    return;
                }
                $this->_redirect('*/*/edit', array('id' => $r->getParam('id')));
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        if (($id = $this->getRequest()->getParam('id')) > 0 ) {
            try {
                $model = Mage::getModel('udropship/vendor');
                /* @var $model Unirgy_Dropship_Model_Vendor */
                $model->setId($id)->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('udropship')->__('Vendor was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    public function gridAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('core/template', 'formkey')
                ->setTemplate('formkey.phtml')
                ->toHtml()
            .
            $this->getLayout()->createBlock('udropship/adminhtml_vendor_grid')->toHtml()
        );
    }

    /**
     * Export subscribers grid to CSV format
     */
    public function exportCsvAction()
    {
        $fileName   = 'vendors.csv';
        $content    = $this->getLayout()->createBlock('udropship/adminhtml_vendor_grid')
            ->getCsv();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Export subscribers grid to XML format
     */
    public function exportXmlAction()
    {
        $fileName   = 'vendors.xml';
        $content    = $this->getLayout()->createBlock('udropship/adminhtml_vendor_grid')
            ->getXml();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function massDeleteAction()
    {
        $certIds = $this->getRequest()->getParam('vendor');
        if (!is_array($certIds)) {
            $this->_getSession()->addError(Mage::helper('udropship')->__('Please select vendor(s)'));
        }
        else {
            try {
                $cert = Mage::getSingleton('udropship/vendor');
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

    public function massStatusAction()
    {
        $modelIds = (array)$this->getRequest()->getParam('vendor');
        $status     = (string)$this->getRequest()->getParam('status');

        try {
            foreach ($modelIds as $modelId) {
                Mage::getModel('udropship/vendor')->load($modelId)->setStatus($status)->save();
            }
            $this->_getSession()->addSuccess(
                Mage::helper('udropship')->__('Total of %d record(s) were successfully updated', count($modelIds))
            );
        }
        catch (Mage_Core_Model_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        catch (Exception $e) {
            $this->_getSession()->addException($e, Mage::helper('udropship')->__('There was an error while updating vendor(s) status'));
        }

        $this->_redirect('*/*/');
    }

    public function massCarrierCodeAction()
    {
        $modelIds = (array)$this->getRequest()->getParam('vendor');
        $carrier_code     = (string)$this->getRequest()->getParam('carrier_code');

        try {
            foreach ($modelIds as $modelId) {
                Mage::getModel('udropship/vendor')->load($modelId)->setCarrierCode($carrier_code)->save();
            }
            $this->_getSession()->addSuccess(
                Mage::helper('udropship')->__('Total of %d record(s) were successfully updated', count($modelIds))
            );
        }
        catch (Mage_Core_Model_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        catch (Exception $e) {
            $this->_getSession()->addException($e, Mage::helper('udropship')->__('There was an error while updating vendor(s) preferred carrier'));
        }

        $this->_redirect('*/*/');
    }

    public function productGridAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('udropship/adminhtml_vendor_edit_tab_products', 'admin.udropship.products')
                ->setVendorId($this->getRequest()->getParam('id'))
                ->toHtml()
        );
    }

    public function shippingGridAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('udropship/adminhtml_vendor_edit_tab_shipping', 'admin.udropship.shipping')
                ->setVendorId($this->getRequest()->getParam('id'))
                ->toHtml()
        );
    }

    public function resaveAllAction()
    {
        ob_implicit_flush();
        echo 'START. ';
        $vendors = Mage::getModel('udropship/vendor')->getCollection();
        foreach ($vendors as $vendor) {
            echo $vendor->getId().', ';
            $vendor->afterLoad();
            $vendor->save();
        }
        echo 'DONE.';
        exit;
    }

	public function withholdToInPayoutAllAction()
    {
        ob_implicit_flush();
        echo 'START. ';
        $vendors = Mage::getModel('udropship/vendor')->getCollection();
        foreach ($vendors as $vendor) {
            echo $vendor->getId().', ';
            $vendor->afterLoad();

            $withhold = array_flip((array)$vendor->getStatementWithholdTotals());
            if (array_key_exists('tax', $withhold)) {
            	$vendor->setData('statement_tax_in_payout', 'exclude_show');
            } else {
            	$vendor->setData('statement_tax_in_payout', 'include');
            }
        	if (array_key_exists('shipping', $withhold)) {
            	$vendor->setData('statement_shipping_in_payout', 'exclude_show');
            } else {
            	$vendor->setData('statement_shipping_in_payout', 'include');
            }

            $vendor->save();
        }
        echo 'DONE.';
        exit;
    }

    public function fixTaxesAction()
    {
        return;

        ob_implicit_flush();
echo "<pre>";
echo 'START. ';
        $totalExtraTax = 0;
        $taxArr = array();
        $vendors = Mage::getModel('udropship/vendor')->getCollection();
        foreach ($vendors as $vendor) {
            $vendor->afterLoad();
            $regions = $vendor->getTaxRegions();
            if (!(is_numeric($regions) || sizeof($regions)==1)) {
                continue;
            }
            $shipments = Mage::getModel('sales/order_shipment')->getCollection()
                ->addAttributeToSelect('order_id')
                ->addAttributeToSelect('base_tax_amount')
                ->addAttributeToFilter('udropship_vendor', $vendor->getId());
            if (!$shipments->count()) {
                continue;
            }
#echo $vendor->getVendorName().'<hr>';
            foreach ($shipments as $shipment) {
                $address = $shipment->getShippingAddress();
                if ($address->getCountryId()!='US' || in_array($address->getRegionId(), (array)$regions)) {
                    continue;
                }
                $order = $shipment->getOrder();
                foreach ($shipment->getAllItems() as $item) {
                    $orderItem = $item->getOrderItem();
                    $tax = (float)$orderItem->getBaseTaxAmount();
                    if ($tax) {
                        $vName = $vendor->getVendorName();
                        $taxArr[$vName]['orders'][$order->getIncrementId()]['items'][$orderItem->getSku()] = $tax;
                        if (empty($taxArr[$vName]['orders'][$order->getIncrementId()]['tax'])) {
                            $taxArr[$vName]['orders'][$order->getIncrementId()]['tax'] = $tax;
                        } else {
                            $taxArr[$vName]['orders'][$order->getIncrementId()]['tax'] += $tax;
                        }
                        if (empty($taxArr[$vName]['tax'])) {
                            $taxArr[$vName]['tax'] = $tax;
                        } else {
                            $taxArr[$vName]['tax'] += $tax;
                        }
                        $totalExtraTax += $tax;
                    }
                }
            }
            #$vendor->save();
        }
print_r($taxArr);
echo 'TOTAL TAX DIFF: '.$totalExtraTax."<hr>";
echo 'DONE.';
echo "</pre>";
        exit;
    }

    public function wysiwygAction()
    {
        $elementId = $this->getRequest()->getParam('element_id', md5(microtime()));
        $storeId = $this->getRequest()->getParam('store_id', 0);
        $storeMediaUrl = Mage::app()->getStore($storeId)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);

        $content = $this->getLayout()->createBlock('udropship/adminhtml_vendor_helper_form_wysiwyg_content', '', array(
            'editor_element_id' => $elementId,
            'store_id'          => $storeId,
            'store_media_url'   => $storeMediaUrl,
        ));

        $this->getResponse()->setBody($content->toHtml());
    }

    protected function _validateSecretKey()
    {
        if (in_array($this->getRequest()->getActionName(), array('resaveAll', 'fixTaxes'))) {
            return true;
        }
        return parent::_validateSecretKey();
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/udropship/vendor');
    }
}
