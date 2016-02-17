<?php
/*------------------------------------------------------------------------
 # SM Deal - Version 1.0
 # Copyright (c) 2014 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

class Sm_Deal_DealController extends Mage_Core_Controller_Front_Action{

 	public function indexAction(){
		$this->loadLayout();
 		if (Mage::helper('deal/deal')->getUseBreadcrumbs()){
			if ($breadcrumbBlock = $this->getLayout()->getBlock('breadcrumbs')){
				$breadcrumbBlock->addCrumb('home', array(
							'label'	=> Mage::helper('deal')->__('Home'), 
							'link' 	=> Mage::getUrl(),
						)
				);
				$breadcrumbBlock->addCrumb('deals', array(
							'label'	=> Mage::helper('deal')->__('Deal'), 
							'link'	=> '',
					)
				);
			}
		}
		$headBlock = $this->getLayout()->getBlock('head');
		if ($headBlock) {
			$headBlock->setTitle(Mage::getStoreConfig('deal/deal/meta_title'));
			$headBlock->setKeywords(Mage::getStoreConfig('deal/deal/meta_keywords'));
			$headBlock->setDescription(Mage::getStoreConfig('deal/deal/meta_description'));
		}
		
		$headBlock = $this->getLayout()->getBlock('head');
			if ($headBlock) {
				$headBlock->setTitle("All Deal Display");
				
				$headBlock->setKeywords("All Deal keyword");
				$headBlock->setDescription("description of all Deal");
			}
		$this->renderLayout();
	}

	public function viewAction(){
		$dealId 	= $this->getRequest()->getParam('id', 0);
		$deal 	= Mage::getModel('deal/deal')
						->setStoreId(Mage::app()->getStore()->getId())
						->load($dealId);
		if (!$deal->getId()){
			$this->_forward('no-route');
		}
		elseif (!$deal->getStatus()){
			$this->_forward('no-route');
		}
		else{
			Mage::register('current_deal_deal', $deal);
			$this->loadLayout();
			if ($root = $this->getLayout()->getBlock('root')) {
				$root->addBodyClass('deal-deal deal-deal' . $deal->getId());
			}
			if (Mage::helper('deal/deal')->getUseBreadcrumbs()){
				if ($breadcrumbBlock = $this->getLayout()->getBlock('breadcrumbs')){
					$breadcrumbBlock->addCrumb('home', array(
								'label'	=> Mage::helper('deal')->__('Home'), 
								'link' 	=> Mage::getUrl(),
							)
					);
					$breadcrumbBlock->addCrumb('deals', array(
								'label'	=> Mage::helper('deal')->__('Deal'), 
								'link'	=> Mage::helper('deal')->getDealsUrl(),
						)
					);
					$breadcrumbBlock->addCrumb('deal', array(
								'label'	=> $deal->getName(), 
								'link'	=> '',
						)
					);
				}
			}
			$headBlock = $this->getLayout()->getBlock('head');
			if ($headBlock) {
				if ($deal->getMetaTitle()){
					$headBlock->setTitle($deal->getMetaTitle());
				}
				else{
					$headBlock->setTitle($deal->getName());
				}
				$headBlock->setKeywords($deal->getMetaKeywords());
				$headBlock->setDescription($deal->getMetaDescription());
			}
			$this->renderLayout();
		}
	}

	public function rssAction(){
		if (Mage::helper('deal/deal')->isRssEnabled()) {
			$this->getResponse()->setHeader('Content-type', 'text/xml; charset=UTF-8');
			$this->loadLayout(false);
			$this->renderLayout();
		}
		else {
			$this->getResponse()->setHeader('HTTP/1.1','404 Not Found');
			$this->getResponse()->setHeader('Status','404 File not found');
			$this->_forward('nofeed','index','rss');
		}
	} 
	public function  getdealAction()
	{
		echo $this->getLayout()
		->createBlock('Mage_Core_Block_Template')
		->setTemplate('sm_deal/default.phtml')
		->toHtml();
	}
}