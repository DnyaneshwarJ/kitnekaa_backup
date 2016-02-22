<?php

class UnirgyCustom_DropshipQuote2sale_Block_Adminhtml_Request_Grid extends Kitnekaa_Quote2SalesCustom_Block_Adminhtml_Request_Grid
{
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('quote2sales/request')->getCollection();
        $collection->getSelect()->joinLeft('kitnekaa_company', 'main_table.company_id=kitnekaa_company.company_id', array('company_name'));
        if (Mage::helper('udquote2sale')->getVendorId() && Mage::helper('udquote2sale')->isSeller()) {
            $collection->getSelect()->joinLeft('vendor_quotes', 'main_table.request_id=vendor_quotes.quote_request_id', array('vendor_id'));
            $collection->addFieldToFilter('vendor_id', array('eq' => Mage::helper('udquote2sale')->getVendorId()));
        }

        $this->setCollection($collection);
        return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
    }

    protected function _prepareMassaction()
    {
        if (!Mage::helper('udquote2sale')->isSeller()) {
            $this->setMassactionIdField('quote2sales_id');
            $this->getMassactionBlock()->setFormFieldName('quote2sales');
            $this->getMassactionBlock()->addItem('delete', array(
                'label' => Mage::helper('quote2sales')->__('Delete'),
                'url' => $this->getUrl('*/*/massDelete'),
                'confirm' => Mage::helper('quote2sales')->__('Are you sure?')
            ));
        }
        return $this;
    }

    protected function _prepareColumns()
    {
        $this->addColumn('created_at', array(
            'header' => Mage::helper('quote2sales')->__('Created At'),
            'width' => '110px',
            'index' => 'created_at',
        ));

        $this->addColumn('request_id', array(
            'header' => Mage::helper('quote2sales')->__('Request ID'),
            'align' => 'right',
            'width' => '40px',
            'index' => 'request_id',
        ));
        $this->addColumnAfter('request_type', array(
            'header'    => Mage::helper('quote2sales')->__('Request Type'),
            'width'     => '100px',
            'index'     => 'request_type',
        ),'request_id');
        $this->addColumnAfter('company_name', array(
            'header'    => Mage::helper('quote2sales')->__('Company'),
            'width'     => '100px',
            'index'     => 'company_name',
        ),'request_type');

        $customers = Mage::helper('quote2sales')->getAllCustomers();
        $this->addColumn('customer', array(
            'header' => Mage::helper('quote2sales')->__('Customer'),
            'width' => '160px',
            'index' => 'customer_id',
            'type' => 'options',
            'options' => $customers,
        ));


       /* $this->addColumn('name', array(
            'header' => Mage::helper('quote2sales')->__('Name on RFQ'),
            'width' => '100px',
            'index' => 'name',
        ));*/

        $this->addColumn('email', array(
            'header' => Mage::helper('quote2sales')->__('Preferred email'),
            'align' => 'left',
            'index' => 'email',
            'width' => '200px',
        ));

        //Display the status of the request
        $this->addColumn('Status', array(
            'header' => Mage::helper('quote2sales')->__('Status'),
            'align' => 'left',
            'width' => '300px',
            'index' => 'request_id',
            'renderer' => new UnirgyCustom_DropshipQuote2sale_Block_Adminhtml_Renderer_RequestStatus()
        ));

        $this->addColumn('Action',
            array(
                'header' => Mage::helper('quote2sales')->__('Action'),
                'width' => '100',
                'type' => 'action',
                'getter' => 'getRequest_id',
                'actions' => array(

                    array(
                        'caption' => Mage::helper('quote2sales')->__('Convert to Quote'),
                        'url' => array('base' => '*/adminhtml_quote_create/index'),
                        'field' => 'request_id'
                    ),
                ),
                'filter' => false,
                'sortable' => false,
                'index' => 'action',
                'is_system' => true,

            ));

        $this->addExportType('*/*/exportCsv', Mage::helper('quote2sales')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('quote2sales')->__('XML'));

        return Mage_Adminhtml_Block_Widget_Grid::_prepareColumns();
    }

    public function getExportTypes()
    {
        if (Mage::helper('udquote2sale')->isSeller()) {
            return false;
        }

        return parent::getExportTypes();
    }
}