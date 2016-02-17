<?php

class Unirgy_Dropship_Block_Adminhtml_Vendor_Statement_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('statementGrid');
        $this->setDefaultSort('vendor_statement_id');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('statement_filter');

    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('udropship/vendor_statement')->getCollection();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $hlp = Mage::helper('udropship');
        $baseUrl = $this->getUrl();

        $this->addColumn('vendor_statement_id', array(
            'header'    => Mage::helper('udropship')->__('ID'),
            'index'     => 'vendor_statement_id',
            'width'     => 10,
            'type'      => 'number',
        ));

        $this->addColumn('created_at', array(
            'header'    => Mage::helper('udropship')->__('Created At'),
            'index'     => 'created_at',
            'type'      => 'datetime',
            'width'     => 150,
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

        $this->addColumn('statement_period', array(
            'header' => Mage::helper('udropship')->__('Period'),
            'index' => 'statement_period',
        ));

        $this->addColumn('total_orders', array(
            'header'    => Mage::helper('udropship')->__('# of Orders'),
            'index'     => 'total_orders',
            'type'      => 'number',
        ));

        if (!$hlp->isStatementAsInvoice()) {
            $this->addColumn('total_payout', array(
                'header'    => Mage::helper('udropship')->__('Total Payment'),
                'index'     => 'total_payout',
                'type'      => 'price',
                'currency_code' => Mage::app()->getStore()->getBaseCurrency()->getCode(),
            ));

            if ($hlp->isUdpayoutActive()) {
                $this->addColumn('total_paid', array(
                    'header'    => Mage::helper('udropship')->__('Total Paid'),
                    'index'     => 'total_paid',
                    'type'      => 'price',
                    'currency_code' => Mage::app()->getStore()->getBaseCurrency()->getCode(),
                ));
                $this->addColumn('total_due', array(
                    'header'    => Mage::helper('udropship')->__('Total Due'),
                    'index'     => 'total_due',
                    'type'      => 'price',
                    'currency_code' => Mage::app()->getStore()->getBaseCurrency()->getCode(),
                ));
            }
        } else {
            $this->addColumn('total_invoice', array(
                'header'    => Mage::helper('udropship')->__('Total Invoice'),
                'index'     => 'total_invoice',
                'type'      => 'price',
                'currency_code' => Mage::app()->getStore()->getBaseCurrency()->getCode(),
            ));
        }

        $this->addColumn('email_sent', array(
            'header' => Mage::helper('udropship')->__('Sent'),
            'index' => 'email_sent',
            'type' => 'options',
            'options' => Mage::getSingleton('udropship/source')->setPath('yesno')->toOptionHash(),
        ));

        $this->addExportType('*/*/exportCsv', Mage::helper('udropship')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('udropship')->__('XML'));
        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('vendor_statement_id');
        $this->getMassactionBlock()->setFormFieldName('statement');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'=> Mage::helper('udropship')->__('Delete'),
             'url'  => $this->getUrl('*/*/massDelete'),
             'confirm' => Mage::helper('udropship')->__('Deleting selected statement(s). Are you sure?')
        ));
        
        $this->getMassactionBlock()->addItem('refresh', array(
             'label'=> Mage::helper('udropship')->__('Refresh'),
             'url'  => $this->getUrl('*/*/massRefresh', array('_current'=>true)),
        ));

        $this->getMassactionBlock()->addItem('download', array(
             'label'=> Mage::helper('udropship')->__('Download/Print'),
             'url'  => $this->getUrl('*/*/massDownload', array('_current'=>true)),
        ));

        $this->getMassactionBlock()->addItem('email', array(
             'label'=> Mage::helper('udropship')->__('Send Emails'),
             'url'  => $this->getUrl('*/*/massEmail', array('_current'=>true)),
             'confirm' => Mage::helper('udropship')->__('Emailing selected statement(s) to vendors. Are you sure?')
        ));

        return $this;
    }
    
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }
}