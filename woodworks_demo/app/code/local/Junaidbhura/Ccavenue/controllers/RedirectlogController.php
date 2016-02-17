<?php
/**
 * Ccavenue Logs controller
 *
 * @category    Controller
 * @package     Junaidbhura_Ccavenue
 * @author      Junaid Bhura <info@junaidbhura.com>
 */

class Junaidbhura_Ccavenue_RedirectlogController extends Mage_Adminhtml_Controller_Action {
	/**
	* Load grid page
	*/
	public function indexAction() {
		$this->_title( $this->__( 'CC Avenue Logs' ) );
		$this->_title( $this->__( 'CC Avenue Redirect Log' ) );
		
		$this->_initAction();
		
		$this->loadLayout();
		$block = Mage::app()->getLayout()->createBlock( 'ccavenue/redirectlog' );
		$this->getLayout()->getBlock( 'content' )->append( $block );
		$this->renderLayout();
	}
	
	/**
	* Init action
	*/
	protected function _initAction() {
		$this->loadLayout()->_setActiveMenu( 'ccavenue/ccavenueredirect' )->_addBreadcrumb( Mage::helper('adminhtml' )->__( 'CC Avenue Redirect Log' ),Mage::helper( 'adminhtml' )->__( 'CC Avenue Redirect Log' ) );
		return $this;
	}
	
	/**
	* Export order grid to CSV format
	*/
	public function exportCsvAction() {
		$file_name  = 'ccavenue_redirect_log.csv';
		$grid       = $this->getLayout()->createBlock( 'ccavenue/redirectlog_grid' );
		$this->_prepareDownloadResponse( $file_name, $grid->getCsvFile() );
	} 
	
	/**
	*  Export order grid to Excel XML format
	*/
	public function exportExcelAction() {
		$file_name  = 'ccavenue_redirect_log.xml';
		$grid       = $this->getLayout()->createBlock( 'ccavenue/redirectlog_grid' );
		$this->_prepareDownloadResponse( $file_name, $grid->getExcelFile( $file_name ) );
	}
}