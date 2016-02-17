<?php
/**
 * Ccavenue Response Log Block
 *
 * @category    Block
 * @package     Gremlin_Ccavenue
 * @author      Junaid Bhura <info@gremlin.com>
 */

class Gremlin_Ccavenue_Block_Responselog extends Mage_Adminhtml_Block_Widget_Grid_Container {
	/**
	* Constructor
	*/
	public function __construct() {
		$this->_controller = 'responselog';
		$this->_blockGroup = 'ccavenue';
		$this->_headerText = Mage::helper( 'ccavenue' )->__( 'CC Avenue Response Log' );
		$this->_addButtonLabel = '';
		parent::__construct();
		$this->_removeButton( 'add' );
	}
}
