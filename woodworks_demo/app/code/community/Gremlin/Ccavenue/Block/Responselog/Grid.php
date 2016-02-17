<?php
/**
 * Ccavenue Response Log Grid Block
 *
 * @category    Block
 * @package     Gremlin_Ccavenue
 * @author      Junaid Bhura <info@gremlin.io>
 */

class Gremlin_Ccavenue_Block_Responselog_Grid extends Mage_Adminhtml_Block_Widget_Grid {
	/**
	* Constructor
	*/
	public function __construct() {
		parent::__construct();
		$this->setId( 'ccavenueresponseGrid' );
		$this->setDefaultSort( 'response_id' );
		$this->setDefaultDir( 'DESC' );
		$this->setSaveParametersInSession( true );
	}
	
	/**
	* Prepare collection
	*/
	protected function _prepareCollection() {
		$collection = Mage::getModel( 'ccavenue/ccavenueresponse' )->getCollection();
		$this->setCollection( $collection );
		
		return parent::_prepareCollection();
	}
	
	/**
	* Prepare columns
	*/
	protected function _prepareColumns() {
		// Add columns to grid
		$this->addColumn( 'response_id', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'ID' ),
			'align' => 'right',
			'width' => '50px',
			'type' => 'number',
			'index' => 'response_id',
		));
		
		$this->addColumn( 'order_id', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'Order ID' ),
			'type' => 'text',
			'index' => 'order_id'
		));

		$this->addColumn( 'created_at', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'Created At' ),
			'type' => 'datetime',
			'index' => 'created_at',
		));
		
		$this->addColumn( 'tracking_id', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'Tracking ID' ),
			'type' => 'text',
			'index' => 'tracking_id'
		));
		
		$this->addColumn( 'bank_ref_no', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'Bank Ref No' ),
			'type' => 'text',
			'index' => 'bank_ref_no'
		));
		
		$this->addColumn( 'order_status', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'Order Status' ),
			'type' => 'text',
			'index' => 'order_status'
		));
		
		$this->addColumn( 'failure_message', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'Failure Message' ),
			'type' => 'text',
			'index' => 'failure_message'
		));
		
		$this->addColumn( 'payment_mode', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'Payment Mode' ),
			'type' => 'text',
			'index' => 'payment_mode'
		));
		
		$this->addColumn( 'card_name', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'Card Name' ),
			'type' => 'text',
			'index' => 'card_name'
		));
		
		$this->addColumn( 'status_code', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'Status Code' ),
			'type' => 'text',
			'index' => 'status_code'
		));
		
		$this->addColumn( 'status_message', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'Status Message' ),
			'type' => 'text',
			'index' => 'status_message'
		));
		
		$this->addColumn( 'currency', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'Currency' ),
			'type' => 'text',
			'index' => 'currency'
		));
		
		$this->addColumn( 'amount', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'Amount' ),
			'type' => 'text',
			'index' => 'amount'
		));
		
		$this->addColumn( 'billing_name', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'Billing Name' ),
			'type' => 'text',
			'index' => 'billing_name'
		));
		
		$this->addColumn( 'billing_address', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'Billing Address' ),
			'type' => 'text',
			'index' => 'billing_address'
		));
		
		$this->addColumn( 'billing_city', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'Billing City' ),
			'type' => 'text',
			'index' => 'billing_city'
		));
		
		$this->addColumn( 'billing_state', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'Billing State' ),
			'type' => 'text',
			'index' => 'billing_state'
		));
		
		$this->addColumn( 'billing_zip', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'Billing Zip' ),
			'type' => 'text',
			'index' => 'billing_zip'
		));
		
		$this->addColumn( 'billing_country', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'Billing Country' ),
			'type' => 'text',
			'index' => 'billing_country'
		));
		
		$this->addColumn( 'billing_tel', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'Billing Tel' ),
			'type' => 'text',
			'index' => 'billing_tel'
		));
		
		$this->addColumn( 'billing_email', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'Billing Email' ),
			'type' => 'text',
			'index' => 'billing_email'
		));
		
		$this->addColumn( 'delivery_name', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'Delivery Name' ),
			'type' => 'text',
			'index' => 'delivery_name'
		));
		
		$this->addColumn( 'delivery_address', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'Delivery Address' ),
			'type' => 'text',
			'index' => 'delivery_address'
		));
		
		$this->addColumn( 'delivery_city', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'Delivery City' ),
			'type' => 'text',
			'index' => 'delivery_city'
		));
		
		$this->addColumn( 'delivery_state', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'Delivery State' ),
			'type' => 'text',
			'index' => 'delivery_state'
		));
		
		$this->addColumn( 'delivery_zip', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'Delivery Zip' ),
			'type' => 'text',
			'index' => 'delivery_zip'
		));
		
		$this->addColumn( 'delivery_country', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'Delivery Country' ),
			'type' => 'text',
			'index' => 'delivery_country'
		));
		
		$this->addColumn( 'delivery_tel', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'Delivery Tel' ),
			'type' => 'text',
			'index' => 'delivery_tel'
		));
		
		$this->addColumn( 'merchant_param1', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'Merchant Param1' ),
			'type' => 'text',
			'index' => 'merchant_param1'
		));
		
		$this->addColumn( 'merchant_param2', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'Merchant Param2' ),
			'type' => 'text',
			'index' => 'merchant_param2'
		));
		
		$this->addColumn( 'merchant_param3', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'Merchant Param3' ),
			'type' => 'text',
			'index' => 'merchant_param3'
		));
		
		$this->addColumn( 'merchant_param4', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'Merchant Param4' ),
			'type' => 'text',
			'index' => 'merchant_param4'
		));
		
		$this->addColumn( 'merchant_param5', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'Merchant Param5' ),
			'type' => 'text',
			'index' => 'merchant_param5'
		));

		$this->addColumn( 'ip_address', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'IP Address' ),
			'type' => 'text',
			'index' => 'ip_address'
		));
		
		$this->addExportType( '*/*/exportCsv', Mage::helper( 'sales' )->__( 'CSV' ) ); 
		$this->addExportType( '*/*/exportExcel', Mage::helper( 'sales' )->__( 'Excel XML' ) );
		
		// Return columns
		return parent::_prepareColumns();
	}
	
	/**
	* Row URL link
	*/
	public function getRowUrl( $row ) {
		return '#';
	}
}