<?php

class Kitnekaa_Quote2SalesCustom_Block_Adminhtml_Quote_Grid extends Bobcares_Quote2Sales_Block_Adminhtml_Quote_Grid{


    protected function _prepareColumns() {

        $this->addColumn('entity_id', array(
            'header' => Mage::helper('quote2sales')->__('Quote ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'entity_id',
        ));

        $this->addColumn('created_at', array(
            'header' => Mage::helper('quote2sales')->__('Created At'),
            'index' => 'created_at',
            'type' => 'datetime',
            'width' => '100px',
        ));

        $this->addColumn('company_name', array(
            'header' => Mage::helper('quote2sales')->__('Company'),
            'width' => '100px',
            'index' => 'company_name',
        ));

        $this->addColumn('customer_fullname', array(
            'header' => Mage::helper('quote2sales')->__('Customer Name'),
            'type'  => 'concat',
            'separator'    => ' ',
            'index' => array('customer_firstname', 'customer_lastname'),
            'filter_index' => "CONCAT(main_table.customer_firstname, ' ',main_table.customer_lastname)",
        ));


        $this->addColumn('customer_email', array(
            'header' => Mage::helper('quote2sales')->__('Email'),
            'index' => 'customer_email',
        ));

        $this->addColumn('quote_request_by', array(
            'header' => Mage::helper('quote2sales')->__('Request By'),
            'index' => 'quote_request_by',
        ));

        $this->addColumn('quote_by', array(
            'header' => Mage::helper('quote2sales')->__('Quote Created By'),
            'index' => 'quote_by',
        ));

        /* Adding request_id column */
        $this->addColumn('request_id', array(
            'header' => Mage::helper('quote2sales')->__('Request ID'),
            'align' => 'center',
            'width' => '50',
            'index' => 'request_id',
            'renderer' => new Bobcares_Quote2Sales_Block_Adminhtml_Renderer_RequestId()
        ));

        $this->addColumn('base_grand_total', array(
            'header' => Mage::helper('quote2sales')->__('G.T. (Base)'),
            'index' => 'base_grand_total',
            'type' => 'currency',
            'currency' => 'base_currency_code',
        ));

        $this->addColumn('is_active', array(
            'header' => Mage::helper('sales')->__('Active'),
            'index' => 'is_active',
            'type' => 'options',
            'width' => '70px',
            'options' => Mage::getSingleton('quote2sales/quote')->getStates(),
        ));


        $this->addColumn('Action', array(
            'header' => Mage::helper('quote2sales')->__('Action'),
            'width' => '100',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => Mage::helper('quote2sales')->__('View'),
                    'url' => array('base' => '*/*/view'),
                    'field' => 'quote_id'
                ),
            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'is_system' => true,
        ));

        $this->addExportType('*/*/exportCsv', Mage::helper('quote2sales')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('quote2sales')->__('XML'));

        return Mage_Adminhtml_Block_Widget_Grid::_prepareColumns();
    }
}
