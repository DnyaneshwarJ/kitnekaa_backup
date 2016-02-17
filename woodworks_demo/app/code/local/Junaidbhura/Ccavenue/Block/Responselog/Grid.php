<?php
/**
 * Ccavenue Response Log Grid Block
 *
 * @category    Block
 * @package     Junaidbhura_Ccavenue
 * @author      Junaid Bhura <info@junaidbhura.com>
 */

class Junaidbhura_Ccavenue_Block_Responselog_Grid extends Mage_Adminhtml_Block_Widget_Grid {
	/**
	* Constructor
	*/
	public function __construct() {
		parent::__construct();
		$this->setId("ccavenueresponseGrid");
		$this->setDefaultSort("ccavenue_response_id");
		$this->setDefaultDir("DESC");
		$this->setSaveParametersInSession(true);
	}
	
	/**
	* Prepare collection
	*/
	protected function _prepareCollection() {
		$collection = Mage::getModel("ccavenue/ccavenueresponse")->getCollection();
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}
	
	/**
	* Prepare columns
	*/
	protected function _prepareColumns() {
		// Add columns to grid
		$this->addColumn( 'ccavenue_response_id', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'ID' ),
			'align' => 'right',
			'width' => '50px',
			'type' => 'number',
			'index' => 'ccavenue_response_id',
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
		$this->addColumn( 'ccavenue_response_dtime', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'Date' ),
			'type' => 'datetime',
			'index' => 'ccavenue_response_dtime',
		));
		$this->addColumn( 'ccavenue_response_ip', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'Redirect IP' ),
			'type' => 'text',
			'index' => 'ccavenue_response_ip',
		));
		$this->addColumn( 'checksum', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'Checksum' ),
			'type' => 'text',
			'index' => 'checksum',
		));
		$this->addColumn( 'authdesc', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'Authdesc' ),
			'type' => 'text',
			'index' => 'authdesc',
		));
		$this->addColumn( 'card_category', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'Card Category' ),
			'type' => 'text',
			'index' => 'card_category',
		));
		$this->addColumn( 'bank_name', array(
			'header' => Mage::helper( 'ccavenue' )->__( 'Bank Name' ),
			'type' => 'text',
			'index' => 'bank_name',
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