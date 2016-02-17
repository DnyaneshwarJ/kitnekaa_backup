<?php

class Kithnekaa_Solr_AjaxController extends Mage_Core_Controller_Front_Action
{
	public function suggestAction(){
		if ( !$this->getRequest()->getParam('q', false) ) {
			// No query received
			$this->getResponse()->setRedirect( Mage::getSingleton('core/url')->getBaseUrl() );
		}
		
		$engine = Mage::getSingleton('kithnekaa_solr/engine');
		$blockType = 'catalogsearch/autocomplete';
		if ( $engine->isWorking() ) {
			$blockType = 'kithnekaa_solr/catalogsearch_autocomplete';
		}
		/** @var Mage_CatalogSearch_Block_Autocomplete $block */
		$block = $this->getLayout()->createBlock($blockType);
		$this->getResponse()->setBody( $block->toHtml() );
		
	}
	
	public function suggestForShoppingListAction(){
		if ( !$this->getRequest()->getParam('q', false) ) {
			// No query received
			$this->getResponse()->setRedirect( Mage::getSingleton('core/url')->getBaseUrl() );
		}
		 
		$engine = Mage::getSingleton('jeroenvermeulen_solarium/engine');
		
		$blockType = 'catalogsearch/autocomplete';
		
		if ( $engine->isWorking() ) {
			$blockType = 'jeroenvermeulen_solarium/catalogsearch_autocomplete';
		}
		 
		$block = $this->getLayout()->createBlock($blockType);
		$this->getResponse()->setBody( $block->toSuggestionlist() );
	}
	
	
}

