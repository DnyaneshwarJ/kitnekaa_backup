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

class Unirgy_Dropship_Block_Adminhtml_Vendor_Statement_Edit_Tab_Payouts extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('statement_payouts');
        $this->setDefaultSort('payout_id');
        $this->setUseAjax(true);
    }

    public function getStatement()
    {
        $statement = Mage::registry('statement_data');
        if (!$statement) {
            $statement = Mage::getModel('udropship/vendor_statement')->load($this->getStatementId());
            Mage::register('statement_data', $statement);
        }
        return $statement;
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('udpayout/payout')->getCollection()
            ->addFieldToFilter('statement_id', $this->getStatement()->getStatementId());
        ;

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('payout_id', array(
            'header'    => Mage::helper('udropship')->__('Payout ID'),
            'index'     => 'payout_id',
        ));
        
        $this->addColumn('statement_id', array(
            'header'    => Mage::helper('udropship')->__('Statement ID'),
            'index'     => 'statement_id',
        ));

        $this->addColumn('vendor_id', array(
            'header' => Mage::helper('udropship')->__('Vendor'),
            'index' => 'vendor_id',
            'type' => 'options',
            'options' => Mage::getSingleton('udropship/source')->setPath('vendors')->toOptionHash(),
            'filter' => 'udropship/vendor_gridColumnFilter'
        ));

        $this->addColumn('payout_type', array(
            'header' => Mage::helper('udropship')->__('Payout Type'),
            'index' => 'payout_type',
            'type' => 'options',
            'options' => Mage::getSingleton('udpayout/source')->setPath('payout_type_internal')->toOptionHash(),
        ));

        $this->addColumn('payout_status', array(
            'header' => Mage::helper('udropship')->__('Payout Status'),
            'index' => 'payout_status',
            'type' => 'options',
            'options' => Mage::getSingleton('udpayout/source')->setPath('payout_status')->toOptionHash(),
        ));
        
        $this->addColumn('transaction_id', array(
            'header'    => Mage::helper('udropship')->__('Transaction ID'),
            'index'     => 'transaction_id',
        ));

        $this->addColumn('total_orders', array(
            'header'    => Mage::helper('udropship')->__('# of Orders'),
            'index'     => 'total_orders',
            'type'      => 'number',
        ));
        
        $this->addColumn('total_payout', array(
            'header' => Mage::helper('udropship')->__('Total Payout'),
            'index' => 'total_payout',
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base'),
        ));
        
        $this->addColumn('total_paid', array(
            'header' => Mage::helper('udropship')->__('Total Paid'),
            'index' => 'total_paid',
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base'),
        ));
        
        $this->addColumn('total_due', array(
            'header' => Mage::helper('udropship')->__('Total Due'),
            'index' => 'total_due',
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base'),
        ));

        $this->addColumn('created_at', array(
            'header'    => Mage::helper('udropship')->__('Created At'),
            'index'     => 'created_at',
            'type'      => 'datetime',
            'width'     => 150,
        ));

        return parent::_prepareColumns();
    }
    
    public function getRowUrl($row)
    {
        return $this->getUrl('adminhtml/udpayoutadmin_payout/edit', array('id' => $row->getId()));
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/payoutGrid', array('_current'=>true));
    }
}
