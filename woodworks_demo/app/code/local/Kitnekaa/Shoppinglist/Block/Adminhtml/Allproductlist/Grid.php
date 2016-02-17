
 <?php 
 class Kitnekaa_Shoppinglist_Block_Adminhtml_Allproductlist_Grid extends Mage_Adminhtml_Block_Widget_Grid
 { 
  protected $customer_id;
      public function __construct()
    {  
      
         parent::__construct();
        $this->setId('shoppinglistadditem_grid');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setFilterVisibility(true);
        $this->customer_id = $this->getRequest()->getParam('customer_id');
    }
  
 protected function _getCollectionClass()
    {
        return 'credittransfer/verifyingcompany';
    }
  
   protected function _prepareCollection()
    {
         $list_id =  $this->getRequest()->getParam('list_id');
         $select = Mage::getModel('catalog/product')->getCollection();
         $select->addAttributeToSelect(array('id','name','price','sku'));
         $this->setCollection($select);

        return parent::_prepareCollection();
        
    }
    protected function _prepareColumns()
    {       
        $this->addColumn('id', array(
          'header'    => 'ID',
          'align'     =>'left',
          'width'     => '10px',
          'index'     => 'entity_id'
        ));

         $this->addColumn('name', array(
            'header' => 'Product Name',
            'index' => 'name',
            'width' => '100px'
            
        ));
        $this->addColumn('price', array(
                'header'    => 'Price',
                'align'     =>'left',
                'width'     => '10px',
                'index'     => 'price'
              ));
          
        $this->addColumn('sku', array(
                'header'    => 'SKU',
                'align'     =>'left',
                'width'     => '10px',
                'index'     => 'sku'
              ));
        
        return parent::_prepareColumns();
    }
  
     public function getRowUrl($row)
    {
       return $this->getCurrentUrl();
    }
  
    public function getGridUrl()
    {  
       return $this->getCurrentUrl();
    }
    
   protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }

		  protected function _prepareMassaction()
		{
			$list_id =  $this->getRequest()->getParam('list_id');

			$formkey = Mage::getSingleton('core/session')->getFormKey();
			$this->setMassactionIdField('entity_id');
			$this->getMassactionBlock()->setFormFieldName('productid');
      $this->getMassactionBlock()->setUseSelectAll(false);
			$ab = Mage::helper("adminhtml")->getUrl("*/shoppinglist/saveitem", array('customer_id' => $this->customer_id,'list_id'=> $list_id));
			$this->getMassactionBlock()->addItem('addnewitem', array(
			'label'=> 'Add Item',
			'url'  => $ab.'&form_key='.$formkey,        
			'confirm' => Mage::helper('shoppinglist')->__('Are you sure?')
			));

			return $this;
		}

  
} 