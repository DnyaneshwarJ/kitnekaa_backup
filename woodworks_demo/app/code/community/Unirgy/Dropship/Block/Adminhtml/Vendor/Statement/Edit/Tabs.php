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

class Unirgy_Dropship_Block_Adminhtml_Vendor_Statement_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('statement_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('udropship')->__('Manage Statements'));
    }

    protected function _beforeToHtml()
    {
        $id = Mage::app()->getRequest()->getParam('id', 0);

        $statement = Mage::registry('statement_data');
        $this->addTab('form_section', array(
            'label'     => Mage::helper('udropship')->__('Statement Information'),
            'title'     => Mage::helper('udropship')->__('Statement Information'),
            'content'   => $this->getLayout()->createBlock('udropship/adminhtml_vendor_statement_edit_tab_form')
                ->setVendorId($id)
                ->toHtml(),
        ));
        if (Mage::helper('udropship')->isUdpayoutActive()) {
            $this->addTab('payouts_section', array(
                'label'     => Mage::helper('udropship')->__('Payouts'),
                'title'     => Mage::helper('udropship')->__('Payouts'),
                'content'   => $this->getLayout()->createBlock('udropship/adminhtml_vendor_statement_edit_tab_payouts', 'statement.payouts.grid')->setVendorId($id)->toHtml(),
            ));
        }
        $this->addTab('rows_section', array(
            'label'     => Mage::helper('udropship')->__('Rows'),
            'title'     => Mage::helper('udropship')->__('Rows'),
            'content'   => $this->getLayout()->createBlock('udropship/adminhtml_vendor_statement_edit_tab_rows', 'statement.rows.grid')->setVendorId($id)->toHtml(),
        ));
        if (Mage::helper('udropship')->isStatementRefundsEnabled()) {
        $this->addTab('refund_rows_section', array(
            'label'     => Mage::helper('udropship')->__('Refunds'),
            'title'     => Mage::helper('udropship')->__('Refunds'),
            'content'   => $this->getLayout()->createBlock('udropship/adminhtml_vendor_statement_edit_tab_refundRows', 'statement.refund_rows.grid')->setVendorId($id)->toHtml(),
        ));
        }
        $this->addTab('adjustments_section', array(
            'label'     => Mage::helper('udropship')->__('Adjustments'),
            'title'     => Mage::helper('udropship')->__('Adjustments'),
            'content'   => $this->getLayout()->createBlock('udropship/adminhtml_vendor_statement_edit_tab_adjustments', 'statement.adjustments.grid')->setVendorId($id)->toHtml(),
        ));

        return parent::_beforeToHtml();
    }
}
