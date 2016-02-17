<?php

class Kitnekaa_Quote2SalesCustom_Block_Adminhtml_Request_Grid extends Bobcares_Quote2Sales_Block_Adminhtml_Request_Grid
{
	 protected function _prepareCollection()
	  {
	      $collection = Mage::getModel('quote2sales/request')->getCollection();
		  $collection->getSelect()->joinLeft('kitnekaa_company', 'main_table.company_id=kitnekaa_company.company_id', array('company_name'));
	      $this->setCollection($collection);
	      return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
	  }

	  protected function _prepareColumns()
	  {

		  $this->addColumnAfter('company_name', array(
			  'header'    => Mage::helper('quote2sales')->__('Company'),
			  'width'     => '100px',
			  'index'     => 'company_name',
		  ),'request_id');
	      return parent::_prepareColumns();
	  }

}