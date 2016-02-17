<?php
/*------------------------------------------------------------------------
 # SM Deal - Version 1.0
 # Copyright (c) 2014 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

class Sm_Deal_Block_Adminhtml_Deal_Widget_Chooser extends Mage_Adminhtml_Block_Widget_Grid{

	public function __construct($arguments=array()){
		parent::__construct($arguments);
		$this->setDefaultSort('entity_id');
		$this->setDefaultDir('ASC');
		$this->setUseAjax(true);
		$this->setDefaultFilter(array('chooser_status' => '1'));
	}

	public function prepareElementHtml(Varien_Data_Form_Element_Abstract $element){
		$uniqId = Mage::helper('core')->uniqHash($element->getId());
		$sourceUrl = $this->getUrl('deal/adminhtml_deal_deal_widget/chooser', array('uniq_id' => $uniqId));
		$chooser = $this->getLayout()->createBlock('widget/adminhtml_widget_chooser')
				->setElement($element)
				->setTranslationHelper($this->getTranslationHelper())
				->setConfig($this->getConfig())
				->setFieldsetId($this->getFieldsetId())
				->setSourceUrl($sourceUrl)
				->setUniqId($uniqId);
		if ($element->getValue()) {
			$deal = Mage::getModel('deal/deal')->load($element->getValue());
			if ($deal->getId()) {
				$chooser->setLabel($deal->getName());
			}
		}
		$element->setData('after_element_html', $chooser->toHtml());
		return $element;
	}

	public function getRowClickCallback(){
		$chooserJsObject = $this->getId();
		$js = '
			function (grid, event) {
				var trElement = Event.findElement(event, "tr");
				var dealId = trElement.down("td").innerHTML.replace(/^\s+|\s+$/g,"");
				var dealTitle = trElement.down("td").next().innerHTML;
				'.$chooserJsObject.'.setElementValue(dealId);
				'.$chooserJsObject.'.setElementLabel(dealTitle);
				'.$chooserJsObject.'.close();
			}
		';
		return $js;
	}

	protected function _prepareCollection(){
		$collection = Mage::getModel('deal/deal')->getCollection();
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	protected function _prepareColumns(){
		$this->addColumn('chooser_id', array(
			'header'	=> Mage::helper('deal')->__('Id'),
			'align' 	=> 'right',
			'index' 	=> 'entity_id',
			'type'		=> 'number',
			'width' 	=> 50
		));
		
		$this->addColumn('chooser_name', array(
			'header'=> Mage::helper('deal')->__('Deal Name'),
			'align' => 'left',
			'index' => 'name',
		));
		if (!Mage::app()->isSingleStoreMode()) {
			$this->addColumn('store_id', array(
				'header'=> Mage::helper('deal')->__('Store Views'),
				'index' => 'store_id',
				'type'  => 'store',
				'store_all' => true,
				'store_view'=> true,
				'sortable'  => false,
			));
		}
		$this->addColumn('chooser_status', array(
			'header'=> Mage::helper('deal')->__('Status'),
			'index' => 'status',
			'type'  => 'options',
			'options'   => array(
				0 => Mage::helper('deal')->__('Disabled'),
				1 => Mage::helper('deal')->__('Enabled')
			),
		));
		return parent::_prepareColumns();
	}

	public function getGridUrl(){
		return $this->getUrl('adminhtml/deal_deal_widget/chooser', array('_current' => true));
	}

	protected function _afterLoadCollection(){
		$this->getCollection()->walk('afterLoad');
		parent::_afterLoadCollection();
	}
}