<?php
class Kitnekaa_Participant_Block_Adminhtml_Shoppinglist extends Mage_Adminhtml_Block_Template {
	public function __construct() {
		$this->_blockGroup = 'kitnekaa_participant';
		$this->_controller = 'adminhtml_shoppinglist';
		parent::__construct ();
		$this->setTemplate ( 'participant/shoppinglist/list.phtml' );
	}
	protected function _prepareLayout() {
		$gridBlock = $this->getLayout ()->createBlock ( 'kitnekaa_participant/adminhtml_shoppinglist_item', 'participant.shoppinglist.item' );
		$this->setChild ( 'item', $gridBlock );
		
		$textBlock = $this->getLayout ()->createBlock ( 'kitnekaa_participant/adminhtml_shoppinglist_text', 'participant.shoppinglist.text' );
		$this->setChild ( 'text', $textBlock );
		return parent::_prepareLayout ();
	}
	public function getParticipantUrl() {
		return $this->getUrl ( '*/*/getParticipant' );
	}
	public function getHeaderText() {
		return Mage::helper ( 'kitnekaa_participant' )->__ ( 'My Shopping List' );
	}
	public function getCompanyNames() {
		$customer = Mage::registry ( 'current_customer' );
		$companyRm = Mage::getResourceModel ( 'kitnekaa_participant/company' );
		$companies = $companyRm->getCompanies ();
		$html = "<option value='0'>Selet Participant</option>";
		foreach ( $companies as $company ) {
			$html .= "<option value='" . $company ['id'] . "'>" . $company ['company_name'] . "</option>";
		}
		return $html;
	}
}