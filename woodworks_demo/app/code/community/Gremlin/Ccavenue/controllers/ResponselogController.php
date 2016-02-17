<?php
/**
 * Ccavenue Logs controller
 *
 * @category    Controller
 * @package     Gremlin_Ccavenue
 * @author      Junaid Bhura <info@gremlin.io>
 */

class Gremlin_Ccavenue_ResponselogController extends Mage_Adminhtml_Controller_Action {
	/**
	* Load grid page
	*/
	public function indexAction() {
		$this->_title( $this->__( 'CC Avenue Logs' ) );
		$this->_title( $this->__( 'CC Avenue Response Log' ) );
		
		$this->_initAction();
		
		$this->loadLayout();
		$block = Mage::app()->getLayout()->createBlock( 'ccavenue/responselog' );
		$this->getLayout()->getBlock( 'content' )->append( $block );
		$this->renderLayout();
	}
	
	/**
	* Init action
	*/
	protected function _initAction() {
		$this->loadLayout()->_setActiveMenu( 'ccavenue/ccavenueresponse' )->_addBreadcrumb( Mage::helper('adminhtml' )->__( 'CC Avenue Response Log' ), Mage::helper( 'adminhtml' )->__( 'CC Avenue Response Log' ) );
		return $this;
	}
	
	/**
	* Export response grid to CSV format
	*/
	public function exportCsvAction() {
		$file_name  = 'ccavenue_response_log.csv';
		$grid       = $this->getLayout()->createBlock( 'ccavenue/responselog_grid' );
		$this->_prepareDownloadResponse( $file_name, $grid->getCsvFile() );
	} 
	
	/**
	*  Export response grid to Excel XML format
	*/
	public function exportExcelAction() {
		$file_name  = 'ccavenue_response_log.xml';
		$grid       = $this->getLayout()->createBlock( 'ccavenue/responselog_grid' );
		$this->_prepareDownloadResponse( $file_name, $grid->getExcelFile( $file_name ) );
	}
}
