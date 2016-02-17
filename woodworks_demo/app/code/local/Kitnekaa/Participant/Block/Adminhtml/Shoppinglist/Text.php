<?php

/**
 * Kitnekaa_Participant_Block_Adminhtml_Shoppinglist_Grid
 *
 * @category   Kitnekaa
 * @package    Kitnekaa_Participant
 * @author      Nanda Khorate
 */
class Kitnekaa_Participant_Block_Adminhtml_Shoppinglist_Text extends Mage_Adminhtml_Block_Widget_Grid {
	protected function _construct() {
		$this->setEmptyText ( Mage::helper ( 'kitnekaa_participant' )->__ ( 'No Shopping List Text Items Found' ) );
	}
	protected function _prepareCollection() {
		$participant = Mage::registry ( 'current_participant' );
		$collection = Mage::getModel ( 'wishlist/text' )->getCollection ()->addCompanyIdFilter ( $participant )->addStatusFilter ();
		
		$this->setCollection ( $collection );
		$this->setPagerVisibility ( false );
		$this->setFilterVisibility ( false );
		return parent::_prepareCollection ();
	}
	protected function _prepareColumns() {
		$this->addColumn ( 'wishlist_text_id', array (
				'header' => Mage::helper ( 'kitnekaa_participant' )->__ ( 'Item ID' ),
				'align' => 'center',
				'index' => 'wishlist_text_id' 
		) );
		
		$this->addColumn ( 'item_name', array (
				'header' => Mage::helper ( 'kitnekaa_participant' )->__ ( 'Item Name' ),
				'index' => 'item_name' 
		) );
		
		$this->addColumn ( 'qty', array (
				'header' => Mage::helper ( 'kitnekaa_participant' )->__ ( 'Quantity' ),
				'index' => 'qty' 
		) );
		$this->addColumn ( 'target_price', array (
				'header' => Mage::helper ( 'kitnekaa_participant' )->__ ( 'Target Price' ),
				'index' => 'target_price' 
		) );
		$this->addColumn ( 'when_need', array (
				'header' => Mage::helper ( 'kitnekaa_participant' )->__ ( 'Wheen Need?' ),
				'index' => 'when_need' 
		) );
		$this->addColumn ( 'purchase_frequency', array (
				'header' => Mage::helper ( 'kitnekaa_participant' )->__ ( 'Purchase Frequency' ),
				'index' => 'purchase_frequency' 
		) );
		
		return $this;
	}
	public function getRowUrl($row) {
		return $this->getUrl ( '*/*/edit', array (
				'id' => $row->getId () 
		) );
	}
}

