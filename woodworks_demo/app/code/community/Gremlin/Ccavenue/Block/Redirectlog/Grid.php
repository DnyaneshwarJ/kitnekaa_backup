<?php
/**
 * Ccavenue Redirect Log Grid Block
 *
 * @category    Block
 * @package     Gremlin_Ccavenue
 * @author      Junaid Bhura <info@gremlin.io>
 */

class Gremlin_Ccavenue_Block_Redirectlog_Grid extends Mage_Adminhtml_Block_Widget_Grid {
	/**
	* Constructor
	*/
	public function __construct() {
		parent::__construct();
		$this->setId( 'ccavenueredirectGrid' );
		$this->setDefaultSort( 'redirect_id' );
		$this->setDefaultDir( 'DESC' );
		$this->setSaveParametersInSession( true );
	}
	
	/**
	* Prepare collection
	*/
	protected function _prepareCollection() {
		$collection = Mage::getModel( 'ccavenue/ccavenueredirect' )->getCollection();
		$this->setCollection( $collection );
		
		return parent::_prepareCollection();
	}
	
	/**
	* Prepare columns
	*/
	protected function _prepareColumns() {
		// Add columns to grid
		$this->addColumn( 'redirect_id', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'ID' ),
			'align' => 'right',
			'width' => '50px',
			'type' => 'number',
			'index' => 'redirect_id',
		));

		$this->addColumn( 'merchant_id', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'Merchant ID' ),
			'type' => 'text',
			'index' => 'merchant_id',
		));

		$this->addColumn( 'amount', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'Amount' ),
			'type' => 'text',
			'index' => 'amount',
		));

		$this->addColumn( 'order_id', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'Order ID' ),
			'type' => 'text',
			'index' => 'order_id',
		));

		$this->addColumn( 'created_at', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'Created At' ),
			'type' => 'datetime',
			'index' => 'created_at',
		));

		$this->addColumn( 'merchant_id', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'Merchant ID' ),
			'type' => 'text',
			'index' => 'merchant_id'
		));

		$this->addColumn( 'amount', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'Amount' ),
			'type' => 'text',
			'index' => 'amount'
		));

		$this->addColumn( 'redirect_url', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'Redirect Url' ),
			'type' => 'text',
			'index' => 'redirect_url'
		));

		$this->addColumn( 'cancel_url', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'Cancel Url' ),
			'type' => 'text',
			'index' => 'cancel_url'
		));

		$this->addColumn( 'currency', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'Currency' ),
			'type' => 'text',
			'index' => 'currency'
		));

		$this->addColumn( 'language', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'Language' ),
			'type' => 'text',
			'index' => 'language'
		));

		$this->addColumn( 'encrypted_data', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'Encrypted Data' ),
			'type' => 'text',
			'index' => 'encrypted_data'
		));

		$this->addColumn( 'access_code', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'Access Code' ),
			'type' => 'text',
			'index' => 'access_code'
		));

		$this->addColumn( 'action', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'Action' ),
			'type' => 'text',
			'index' => 'action'
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

		$this->addColumn( 'ip_address', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'IP Address' ),
			'type' => 'text',
			'index' => 'ip_address'
		));
		
		$this->addExportType( '*/*/exportCsv', Mage::helper('sales')->__( 'CSV' ) ); 
		$this->addExportType( '*/*/exportExcel', Mage::helper('sales')->__( 'Excel XML' ) );
		
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