<?php

class Unirgy_Dropship_Udropshipadmin_Vendor_StatementController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout();

        $hlp = Mage::helper('udropship');

        $this->_setActiveMenu('sales/udropship');
        $this->_addBreadcrumb(Mage::helper('udropship')->__('Vendor Statements'), Mage::helper('udropship')->__('Vendor Statements'));
        $this->_addContent($this->getLayout()->createBlock('udropship/adminhtml_vendor_statement'));

        $this->renderLayout();
    }

    public function newAction()
    {
        $this->loadLayout();

        $hlp = Mage::helper('udropship');

        $this->_setActiveMenu('sales/udropship');
        $this->_addBreadcrumb(Mage::helper('udropship')->__('Vendor Statements'), Mage::helper('udropship')->__('Vendor Statements'));
        $this->_addBreadcrumb(Mage::helper('udropship')->__('Generate'), Mage::helper('udropship')->__('Generate'));
        $this->_addContent($this->getLayout()->createBlock('udropship/adminhtml_vendor_statement_new'));

        $this->renderLayout();
    }

    public function newPostAction()
    {
        if (!$this->getRequest()->isPost()) {
            $this->_redirect('new');
            return;
        }
        $hlp = Mage::helper('udropship');

        $dateFrom = $this->getRequest()->getParam('date_from');
        $dateTo = $this->getRequest()->getParam('date_to');

        $datetimeFormatInt = Varien_Date::DATETIME_INTERNAL_FORMAT;
        $dateFormat = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        if ($this->getRequest()->getParam('use_locale_timezone')) {
            $dateFrom = Mage::helper('udropship')->dateLocaleToInternal($dateFrom, $dateFormat, true);
            $dateTo = Mage::helper('udropship')->dateLocaleToInternal($dateTo, $dateFormat, true);
            $dateTo = Mage::app()->getLocale()->date($dateTo, $datetimeFormatInt, null, false)
                ->addDay(1)->subSecond(1)->toString($datetimeFormatInt);
        } else {
            $dateFrom = Mage::app()->getLocale()->date($dateFrom, $dateFormat, null, false)->toString($datetimeFormatInt);
            $dateTo = Mage::app()->getLocale()->date($dateTo, $dateFormat, null, false)->addDay(1)->subSecond(1)->toString($datetimeFormatInt);
        }

        if ($this->getRequest()->getParam('all_vendors')) {
            $vendors = Mage::getModel('udropship/vendor')->getCollection()
                ->addFieldToFilter('status', 'A')
                ->getAllIds();
        } else {
            $vendors = $this->getRequest()->getParam('vendor_ids');
        }
        $period = $this->getRequest()->getParam('statement_period');
        if (!$period) {
            $period = date('ym', strtotime($dateFrom));
        }

        $n = sizeof($vendors);
        $i = 0;
        ob_implicit_flush();
        echo "<html><body>Generating {$n} vendor statements<hr/>";

        $generator = Mage::getModel('udropship/pdf_statement');
        foreach ($vendors as $vId) {
            echo "Vendor ID {$vId} (".(++$i)."/{$n}): ";
            try {
                $statement = Mage::getModel('udropship/vendor_statement');
                if ($statement->load("{$vId}-{$period}", 'statement_id')->getId()) {
                    echo "<span style='color:#888'>ALREADY EXISTS</span>.<br/>";
                    continue;
                }
                $statement->addData(array(
                    'vendor_id' => $vId,
                    'order_date_from' => $dateFrom,
                    'order_date_to' => $dateTo,
                    'statement_id' => "{$vId}-{$period}",
                    'statement_date' => now(),
                    'statement_period' => $period,
                    'statement_filename' => "statement-{$vId}-{$period}.pdf",
                    'created_at' => now(),
                    'use_locale_timezone' => $this->getRequest()->getParam('use_locale_timezone')
                ));

                $statement->fetchOrders();

                $statement->save();
            } catch (Exception $e) {
                echo "<span style='color:#F00'>ERROR</span>: ".$e->getMessage()."<br/>";
                continue;
            }
            echo "<span style='color:#0F0'>DONE</span>.<br/>";
        }

        $redirectUrl = Mage::helper('adminhtml')->getUrl('adminhtml/udropshipadmin_vendor_statement');
        echo "<hr>".Mage::helper('udropship')->__('All done, <a href="%s">click here</a> to be redirected to statements grid.', $redirectUrl);
        exit;
    }

    public function editAction()
    {
        $this->loadLayout();

        $this->_setActiveMenu('sales/udropship');
        $this->_addBreadcrumb(Mage::helper('udropship')->__('Statements'), Mage::helper('udropship')->__('Statements'));

        $this->_addContent($this->getLayout()->createBlock('udropship/adminhtml_vendor_statement_edit'))
            ->_addLeft($this->getLayout()->createBlock('udropship/adminhtml_vendor_statement_edit_tabs'));

        $this->renderLayout();
    }
    
    public function saveAction()
    {
        if ( $this->getRequest()->getPost() ) {
            $r = $this->getRequest();
            $hlp = Mage::helper('udropship');
            try {
                if (($id = $this->getRequest()->getParam('id')) > 0
                    && ($statement = Mage::getModel('udropship/vendor_statement')->load($id)) 
                    && $statement->getId()
                ) {
                    $statement->setNotes($this->getRequest()->getParam('notes'));
                    if (($adjArr = $this->getRequest()->getParam('adjustment'))
                        && is_array($adjArr) && is_array($adjArr['amount'])
                    ) {
                        foreach ($adjArr['amount'] as $k => $adjAmount) {
                            if (is_numeric($adjAmount)) {
                                $createdAdj = $statement->createAdjustment($adjAmount)
                                    ->setComment(isset($adjArr['comment'][$k]) ? $adjArr['comment'][$k] : '')
                                    ->setPoType(isset($adjArr['po_type'][$k]) ? $adjArr['po_type'][$k] : null)
                                    ->setUsername(Mage::getSingleton('admin/session')->getUser()->getUsername())
                                    ->setPoId(isset($adjArr['po_id'][$k]) ? $adjArr['po_id'][$k] : null);
                                $statement->addAdjustment($createdAdj);
                            }
                        }
                        $statement->finishStatement();
                    }
                     
                    $statement->save();
                    if ($this->getRequest()->getParam('refresh_flag')) {
                        $statement->fetchOrders()->save();
                        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('udropship')->__('Statement was successfully refreshed'));
                    }
                    if ($this->getRequest()->getParam('pay_flag')) {
                        $this->_redirect('adminhtml/udpayoutadmin_payout/edit', array('id'=>$statement->createPayout()->save()->getId()));
                        return;
                    }
                } else {
                    Mage::throwException(Mage::helper('udropship')->__("Statement '%s' no longer exists", $id));
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('udropship')->__('Statement was successfully saved'));

                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::logException($e);
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/');
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        if (($id = $this->getRequest()->getParam('id')) > 0 ) {
            try {
                $model = Mage::getModel('udropship/vendor_statement');
                $model->load($id)->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('udropship')->__('Statement was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::logException($e);
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }
    
    public function payoutGridAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('udropship/adminhtml_vendor_statement_edit_tab_payouts', 'admin.statement.payouts')
                ->setStatementId($this->getRequest()->getParam('id'))
                ->toHtml()
        );
    }
    
    public function rowGridAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('udropship/adminhtml_vendor_statement_edit_tab_rows', 'admin.statement.rows')
                ->setStatementId($this->getRequest()->getParam('id'))
                ->toHtml()
        );
    }
    public function refundRowGridAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('udropship/adminhtml_vendor_statement_edit_tab_refundRows', 'admin.statement.refundRows')
                ->setStatementId($this->getRequest()->getParam('id'))
                ->toHtml()
        );
    }
    
    public function adjustmentGridAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('udropship/adminhtml_vendor_statement_edit_tab_adjustments', 'admin.statement.adjustments')
                ->setStatementId($this->getRequest()->getParam('id'))
                ->toHtml()
        );
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/udropship/vendor');
    }

    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('udropship/adminhtml_vendor_statement_grid')->toHtml()
        );
    }

    /**
     * Export subscribers grid to CSV format
     */
    public function exportCsvAction()
    {
        $fileName   = 'vendors.csv';
        $content    = $this->getLayout()->createBlock('udropship/adminhtml_vendor_statement_grid')
            ->getCsv();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Export subscribers grid to XML format
     */
    public function exportXmlAction()
    {
        $fileName   = 'vendors.xml';
        $content    = $this->getLayout()->createBlock('udropship/adminhtml_vendor_statement_grid')
            ->getXml();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function massDeleteAction()
    {
        $objIds = (array)$this->getRequest()->getParam('statement');
        if (!is_array($objIds)) {
            $this->_getSession()->addError(Mage::helper('udropship')->__('Please select statement(s)'));
        }
        else {
            try {
                $obj = Mage::getSingleton('udropship/vendor_statement');
                foreach ($objIds as $objId) {
                    Mage::getModel('udropship/vendor_statement')->load($objId)->setId($objId)->delete();
                }
                $this->_getSession()->addSuccess(
                    Mage::helper('udropship')->__('Total of %d record(s) were successfully deleted', count($objIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
    
    public function massRefreshAction()
    {
        $objIds = (array)$this->getRequest()->getParam('statement');
        if (!is_array($objIds)) {
            $this->_getSession()->addError(Mage::helper('udropship')->__('Please select statement(s)'));
        }
        else {
            try {
                foreach ($objIds as $objId) {
                    $st = Mage::getModel('udropship/vendor_statement')->load($objId);
                    if ($st->getId()) {
                        $st->fetchOrders()->save();
                    }
                }
                $this->_getSession()->addSuccess(
                    Mage::helper('udropship')->__('Total of %d record(s) were successfully refreshed', count($objIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function massDownloadAction()
    {
        $objIds = (array)$this->getRequest()->getParam('statement');
        if (!is_array($objIds)) {
            $this->_getSession()->addError(Mage::helper('udropship')->__('Please select statement(s)'));
        }
        try {
            $generator = Mage::getModel('udropship/pdf_statement')->before();
            $statement = Mage::getModel('udropship/vendor_statement');
            foreach ($objIds as $id) {
                $statement = Mage::getModel('udropship/vendor_statement')->load($id);
                if (!$statement->getId()) {
                    continue;
                }
                $generator->addStatement($statement);
            }
            $pdf = $generator->getPdf();
            if (empty($pdf->pages)) {
                Mage::throwException(Mage::helper('udropship')->__('No statements found to print'));
            }
            $generator->insertTotalsPage()->after();
            Mage::helper('udropship')->sendDownload('statements.pdf', $pdf->render(), 'application/x-pdf');
        }
        catch (Mage_Core_Model_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        catch (Exception $e) {
            $this->_getSession()->addException($e, Mage::helper('udropship')->__('There was an error while download vendor statement(s): %s', $e->getMessage()));
        }

        $this->_redirect('*/*/');
    }

    public function massEmailAction()
    {
        $objIds = (array)$this->getRequest()->getParam('statement');
        if (!is_array($objIds)) {
            $this->_getSession()->addError(Mage::helper('udropship')->__('Please select statement(s)'));
        }
        try {
            $statement = Mage::getModel('udropship/vendor_statement');
            foreach ($objIds as $id) {
                $statement = Mage::getModel('udropship/vendor_statement')->load($id);
                $statement->send();
            }
            Mage::helper('udropship')->processQueue();
            $this->_getSession()->addSuccess(
                Mage::helper('udropship')->__('Total of %d statement(s) have been sent', count($objIds))
            );

        }
        catch (Mage_Core_Model_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        catch (Exception $e) {
            $this->_getSession()->addException($e, Mage::helper('udropship')->__('There was an error while download vendor statement(s): %s', $e->getMessage()));
        }

        $this->_redirect('*/*/');
    }
}
