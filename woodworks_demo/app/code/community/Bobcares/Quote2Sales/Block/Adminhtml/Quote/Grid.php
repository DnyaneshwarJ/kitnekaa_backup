<?php

/**
 * Block functions for OS listing
 * @category    Bobcares
 * @package     Bobcares_Quote2Sales
 */
class Bobcares_Quote2Sales_Block_Adminhtml_Quote_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('quoteGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    /*
     * Sets up the OS data from database
     */

    protected function _prepareCollection() {
//      $collection = Mage::getResourceModel("sales/quote_collection");
        $collection = Mage::getResourceModel('sales/quote_collection')
                ->addFieldToSelect('*')
                ->addFieldToFilter('is_active', 1)
                ->addFieldToFilter("grand_total", array("gt" => 0))
                ->setOrder('created_at', 'desc');
        $this->addRequestIdAttributeToCollection($collection);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /*
     * Sets up the columns in the grid
     */

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

        $this->addColumn('customer_firstname', array(
            'header' => Mage::helper('quote2sales')->__('First Name'),
            'index' => 'customer_firstname',
        ));

        $this->addColumn('customer_lastname', array(
            'header' => Mage::helper('quote2sales')->__('Last Name'),
            'index' => 'customer_lastname',
        ));

        $this->addColumn('customer_email', array(
            'header' => Mage::helper('quote2sales')->__('Email'),
            'index' => 'customer_email',
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

        /*
          $this->addColumn('grand_total', array(
          'header' => Mage::helper('sales')->__('G.T. (Purchased)'),
          'index' => 'grand_total',
          'type'  => 'currency',
          'currency' => 'order_currency_code',
          ));
         */

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

        return parent::_prepareColumns();
    }

    /*
     * This function displays the side mass delete option
     */

    protected function _prepareMassaction() {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('quote_ids');


        $this->getMassactionBlock()->addItem('delete', array(
            'label' => Mage::helper('quote2sales')->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('quote2sales')->__('Are you sure?')
        ));

        return $this;
    }

    /*
     * Gets the url of each row
     * @param $row is the array with the data in each row
     */

    public function getRowUrl($row) {
        return $this->getUrl('*/*/view', array('quote_id' => $row->getId()));
    }

    /**
     * @Desc Method to add request Id to the Quote collection in grid
     * @param  $collection The collection to which the corresponding request Id has to be added
     * 
     */
    public function addRequestIdAttributeToCollection($collection) {

        /* For all the quotes in collection add the recorrespoding request id */
        foreach ($collection as $quote) {
            $quoteId = $quote['entity_id'];
            $statusCollection = Mage::getModel('quote2sales/requeststatus')->getCollection();
            $requestId = $statusCollection->addFieldToSelect('request_id')
                            ->addFieldToFilter('quote_id', ((int) $quoteId))->getFirstItem()->getData('request_id');

            /* If request Id exists */
            if ($requestId) {

                $quote['request_id'] = $requestId;
            }
        }
    }

}
