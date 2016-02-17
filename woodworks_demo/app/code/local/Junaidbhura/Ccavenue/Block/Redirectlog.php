<?php
/**
 * Ccavenue Redirect Log Block
 *
 * @category    Block
 * @package     Junaidbhura_Ccavenue
 * @author      Junaid Bhura <info@junaidbhura.com>
 */

class Junaidbhura_Ccavenue_Block_Redirectlog extends Mage_Adminhtml_Block_Widget_Grid_Container {
	/**
	* Constructor
	*/
	public function __construct() {
		$this->_controller = 'redirectlog';
		$this->_blockGroup = 'ccavenue';
		$this->_headerText = Mage::helper( 'ccavenue' )->__( 'CC Avenue Redirect Log' );
		$this->_addButtonLabel = '';
		parent::__construct();
		$this->_removeButton( 'add' );
	}
}