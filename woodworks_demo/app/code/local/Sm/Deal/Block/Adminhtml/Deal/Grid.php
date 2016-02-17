<?php
/*------------------------------------------------------------------------
 # SM Deal - Version 1.0
 # Copyright (c) 2014 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

class Sm_Deal_Block_Adminhtml_Deal_Grid extends Mage_Adminhtml_Block_Widget_Grid{

	public function __construct(){
		parent::__construct();
		$this->setId('dealGrid');
		$this->setDefaultSort('entity_id');
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(true);
		$this->setUseAjax(true);
	}

	protected function _prepareCollection(){
		$collection = Mage::getModel('deal/deal')->getCollection();
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	protected function _prepareColumns(){
		$this->addColumn('entity_id', array(
			'header'	=> Mage::helper('deal')->__('Id'),
			'index'		=> 'entity_id',
			'type'		=> 'number'
		));
		$this->addColumn('name', array(
			'header'=> Mage::helper('deal')->__('Deal Name'),
			'index' => 'name',
			'type'	 	=> 'text',

		));
		$this->addColumn('start_date', array(
			'header'=> Mage::helper('deal')->__('Start Date'),
			'index' => 'start_date',
			'type'	 	=> 'date',

		));
		$this->addColumn('end_date', array(
			'header'=> Mage::helper('deal')->__('End Date'),
			'index' => 'end_date',
			'type'	 	=> 'date',

		));
		$this->addColumn('url_key', array(
			'header'	=> Mage::helper('deal')->__('URL key'),
			'index'		=> 'url_key',
		));
		$this->addColumn('status', array(
			'header'	=> Mage::helper('deal')->__('Status'),
			'index'		=> 'status',
			'type'		=> 'options',
			'options'	=> array(
				'1' => Mage::helper('deal')->__('Enabled'),
				'0' => Mage::helper('deal')->__('Disabled'),
			)
		));
		if (!Mage::app()->isSingleStoreMode()) {
			$this->addColumn('store_id', array(
				'header'=> Mage::helper('deal')->__('Store Views'),
				'index' => 'store_id',
				'type'  => 'store',
				'store_all' => true,
				'store_view'=> true,
				'sortable'  => false,
				'filter_condition_callback'=> array($this, '_filterStoreCondition'),
			));
		}

		$this->addColumn('action',
			array(
				'header'=>  Mage::helper('deal')->__('Action'),
				'width' => '100',
				'type'  => 'action',
				'getter'=> 'getId',
				'actions'   => array(
					array(
						'caption'   => Mage::helper('deal')->__('Edit'),
						'url'   => array('base'=> '*/*/edit'),
						'field' => 'id'
					)
				),
				'filter'=> false,
				'is_system'	=> true,
				'sortable'  => false,
		));
		$this->addExportType('*/*/exportCsv', Mage::helper('deal')->__('CSV'));
		$this->addExportType('*/*/exportExcel', Mage::helper('deal')->__('Excel'));
		$this->addExportType('*/*/exportXml', Mage::helper('deal')->__('XML'));
		return parent::_prepareColumns();
	}

	protected function _prepareMassaction(){
		$this->setMassactionIdField('entity_id');
		$this->getMassactionBlock()->setFormFieldName('deal');
		$this->getMassactionBlock()->addItem('delete', array(
			'label'=> Mage::helper('deal')->__('Delete'),
			'url'  => $this->getUrl('*/*/massDelete'),
			'confirm'  => Mage::helper('deal')->__('Are you sure?')
		));
		$this->getMassactionBlock()->addItem('status', array(
			'label'=> Mage::helper('deal')->__('Change status'),
			'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
			'additional' => array(
				'status' => array(
						'name' => 'status',
						'type' => 'select',
						'class' => 'required-entry',
						'label' => Mage::helper('deal')->__('Status'),
						'values' => array(
								'1' => Mage::helper('deal')->__('Enabled'),
								'0' => Mage::helper('deal')->__('Disabled'),
						)
				)
			)
		));
		return $this;
	}

	public function getRowUrl($row){
		return $this->getUrl('*/*/edit', array('id' => $row->getId()));
	}

	public function getGridUrl(){
		return $this->getUrl('*/*/grid', array('_current'=>true));
	}

	protected function _afterLoadCollection(){
		$this->getCollection()->walk('afterLoad');
		parent::_afterLoadCollection();
	}

	protected function _filterStoreCondition($collection, $column){
		if (!$value = $column->getFilter()->getValue()) {
        	return;
		}
		$collection->addStoreFilter($value);
		return $this;
    }
}