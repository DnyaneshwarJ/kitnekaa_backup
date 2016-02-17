<?php

class Bobcares_Quote2Sales_Block_Adminhtml_Request_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	  public function __construct()
	  {
	      parent::__construct();
	      $this->setId('requestGrid');
	      $this->setDefaultSort('created_at');
	      $this->setDefaultDir('DESC');
	      $this->setSaveParametersInSession(true);
	  }
	
	  
	 protected function _prepareCollection()
	  {
	      $collection = Mage::getModel('quote2sales/request')->getCollection();
	      
//	      ->addFilter('latest', 'y');
	      $this->setCollection($collection);
	      return parent::_prepareCollection();
	  }

	  protected function _prepareColumns()
	  {
                  $this->addColumn('created_at', array(
	          'header'    => Mage::helper('quote2sales')->__('Created At'),
	          'align'     =>'right',
	          'width'     => '100px',
	          'index'     => 'created_at',
	      ));
	  	
	      $this->addColumn('request_id', array(
	          'header'    => Mage::helper('quote2sales')->__('Request ID'),
	          'align'     =>'right',
	          'width'     => '70px',
	          'index'     => 'request_id',
	      ));
	        //$customers = Mage::helper('quote2sales')->getAllCustomers();
	      $customers = Mage::helper('quote2sales')->getAllCustomers();
	      $this->addColumn('customer', array(
				'header'    => Mage::helper('quote2sales')->__('Customer'),
				'width'     => '100px',
				'index'     => 'customer_id',
	          'type'      => 'options',
	          'options'   => $customers,
	      ));
	      
	      
	      $this->addColumn('name', array(
	      		'header'    => Mage::helper('quote2sales')->__('Name on RFQ'),
	      		'width'     => '100px',
	      		'index'     => 'name',
	      ));
	      
	      $this->addColumn('email', array(
	          'header'    => Mage::helper('quote2sales')->__('Preferred email'),
	          'align'     =>'left',
	          'index'     => 'email',
	          'width'     => '200px',
	      ));
	     /* $this->addColumn('phone', array(
	      		'header'    => Mage::helper('quote2sales')->__('Preferred Contact no'),
	      		'align'     =>'left',
	      		'index'     => 'phone',
	      		'width'     => '100px',
	      ));*/
	/*
	      $this->addColumn('ip', array(
	          'header'    => Mage::helper('quote2sales')->__('IP'),
	          'align'     =>'left',
	          'index'     => 'ip',
	          'width'     => '200px',
	      ));
	
	 */   
              //Display the status of the request
	      $this->addColumn('Status', array(
			'header'	=>	Mage::helper('quote2sales')->__('Status'),
			'align'		=>	'left',
			'width'		=>	'180px',
			'index'		=>	'request_id',
			'renderer'      =>      new Bobcares_Quote2Sales_Block_Adminhtml_Renderer_RequestStatus()
                ));
              
	      $this->addColumn('Action',
	            array(
	                'header'    =>  Mage::helper('quote2sales')->__('Action'),
	                'width'     => '100',
	                'type'      => 'action',
	                'getter'    => 'getRequest_id',
	                'actions'   => array(
	                   
	                	array(	                			
	                				'caption'   => Mage::helper('quote2sales')->__('Convert to Quote'),
	                				'url'       => array('base'=> '*/adminhtml_quote_create/index'),
									'field'     => 'request_id'
	                	),
	                 ),
	                'filter'    => false,
	                'sortable'  => false,
	                'index'     => 'action',
	                'is_system' => true,
	                 
	                 ));
			
			$this->addExportType('*/*/exportCsv', Mage::helper('quote2sales')->__('CSV'));
			$this->addExportType('*/*/exportXml', Mage::helper('quote2sales')->__('XML'));
		  
	      return parent::_prepareColumns();
	  }

	  protected function _prepareMassaction()
	  	{
	        $this->setMassactionIdField('quote2sales_id');
	        $this->getMassactionBlock()->setFormFieldName('quote2sales');
	
	
	        $this->getMassactionBlock()->addItem('delete', array(
	             'label'    => Mage::helper('quote2sales')->__('Delete'),
	             'url'      => $this->getUrl('*/*/massDelete'),
	             'confirm'  => Mage::helper('quote2sales')->__('Are you sure?')
	        ));
	
	        return $this;
	   }
	
	public function getRowUrl($row)
	{
		   return $this->getUrl('*/*/view', array('id' => $row->getId()));
	}

}