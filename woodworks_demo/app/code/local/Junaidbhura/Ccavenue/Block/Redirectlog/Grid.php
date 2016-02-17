<?php
/**
 * Ccavenue Redirect Log Grid Block
 *
 * @category    Block
 * @package     Junaidbhura_Ccavenue
 * @author      Junaid Bhura <info@junaidbhura.com>
 */

class Junaidbhura_Ccavenue_Block_Redirectlog_Grid extends Mage_Adminhtml_Block_Widget_Grid {
	/**
	* Constructor
	*/
	public function __construct() {
		parent::__construct();
		$this->setId("ccavenueredirectGrid");
		$this->setDefaultSort("ccavenue_redirect_id");
		$this->setDefaultDir("DESC");
		$this->setSaveParametersInSession(true);
	}
	
	/**
	* Prepare collection
	*/
	protected function _prepareCollection() {
		$collection = Mage::getModel("ccavenue/ccavenueredirect")->getCollection();
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}
	
	/**
	* Prepare columns
	*/
	protected function _prepareColumns() {
		// Add columns to grid
		$this->addColumn( 'ccavenue_redirect_id', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'ID' ),
			'align' => 'right',
			'width' => '50px',
			'type' => 'number',
			'index' => 'ccavenue_redirect_id',
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
		$this->addColumn( 'ccavenue_redirect_dtime', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'Date' ),
			'type' => 'datetime',
			'index' => 'ccavenue_redirect_dtime',
		));
		$this->addColumn( 'ccavenue_redirect_ip', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'Redirect IP' ),
			'type' => 'text',
			'index' => 'ccavenue_redirect_ip',
		));
		$this->addColumn( 'redirect_url', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'Redirect URL' ),
			'type' => 'text',
			'index' => 'redirect_url',
		));
		$this->addColumn( 'checksum', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'Checksum' ),
			'type' => 'text',
			'index' => 'checksum',
		));
		$this->addColumn( 'billing_cust_name', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'Billing Name' ),
			'type' => 'text',
			'index' => 'billing_cust_name',
		));
		$this->addColumn( 'billing_cust_address', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'Billing Name' ),
			'type' => 'text',
			'index' => 'billing_cust_address',
		));
		$this->addColumn( 'billing_cust_country', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'Billing Country' ),
			'type' => 'text',
			'index' => 'billing_cust_country',
		));
		$this->addColumn( 'billing_cust_state', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'Billing State' ),
			'type' => 'text',
			'index' => 'billing_cust_state',
		));
		$this->addColumn( 'billing_zip', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'Billing ZIP' ),
			'type' => 'text',
			'index' => 'billing_zip',
		));
		$this->addColumn( 'billing_cust_city', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'Billing City' ),
			'type' => 'text',
			'index' => 'billing_cust_city',
		));
		$this->addColumn( 'billing_zip_code', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'Billing ZIP' ),
			'type' => 'text',
			'index' => 'billing_zip_code',
		));
		$this->addColumn( 'billing_cust_tel', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'Billing Tel No' ),
			'type' => 'text',
			'index' => 'billing_cust_tel',
		));
		$this->addColumn( 'billing_cust_email', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'Billing Email' ),
			'type' => 'text',
			'index' => 'billing_cust_email',
		));
		$this->addColumn( 'delivery_cust_name', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'Delivery Name' ),
			'type' => 'text',
			'index' => 'delivery_cust_name',
		));
		$this->addColumn( 'delivery_cust_address', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'Delivery Name' ),
			'type' => 'text',
			'index' => 'delivery_cust_address',
		));
		$this->addColumn( 'delivery_cust_country', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'Delivery Country' ),
			'type' => 'text',
			'index' => 'delivery_cust_country',
		));
		$this->addColumn( 'delivery_cust_state', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'Delivery State' ),
			'type' => 'text',
			'index' => 'delivery_cust_state',
		));
		$this->addColumn( 'delivery_cust_tel', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'Delivery Tel No' ),
			'type' => 'text',
			'index' => 'delivery_cust_tel',
		));
		$this->addColumn( 'delivery_cust_city', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'Delivery City' ),
			'type' => 'text',
			'index' => 'delivery_cust_city',
		));
		$this->addColumn( 'delivery_zip_code', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'Delivery ZIP' ),
			'type' => 'text',
			'index' => 'delivery_zip_code',
		));
		
		$this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV')); 
		$this->addExportType('*/*/exportExcel', Mage::helper('sales')->__('Excel XML'));
		
		// Return columns
		return parent::_prepareColumns();
	}
	
	/**
	* Row URL link
	*/
	public function getRowUrl($row) {
		return '#';
	}
}