


 <?php 
 class Kitnekaa_Shoppinglist_Block_Adminhtml_Editlist_Grid extends Mage_Adminhtml_Block_Widget_Grid
 {
      protected $editflag = 0;
      public function __construct()
    {  
      
        parent::__construct();
        $this->setId('shoppinglistedit_grid');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setFilterVisibility(true);
      
    }
  
 protected function _getCollectionClass()
    {
        return 'credittransfer/verifyingcompany';
    }
  
   protected function _prepareCollection()
    {
         $list_id =  $this->getRequest()->getParam('list_id');
        //$customer_id = Mage::helper('credittransfer')->findcompany();
         $select = Mage::getModel('shoppinglist/shoppinglistitems')->getCollection();
         $select->addFieldToFilter('main_table.list_id',array('eq'=> $list_id));
         $select->getSelect();
         $this->setCollection($select);

        return parent::_prepareCollection();
         
    }
    protected function _prepareColumns()
    {       
        $this->addColumn('id', array(
          'header'    => 'ID',
          'align'     =>'left',
          'width'     => '10px',
          'index'     => 'id'
        ));

         $this->addColumn('product_id', array(
          'header'    => 'Product Id',
          'align'     =>'left',
          'width'     => '10px',
          'index'     => 'product_id'
        ));

         $this->addColumn('item_name', array(
            'header' => 'Product Name',
            'index' => 'item_name',
            'width' => '100px'
            
        ));

        $this->addColumn('sku', array(
            'header' => 'SKU',
            'index' => 'sku',
            'width' => '100px'
            
        ));
 

        $this->addColumn('Action', array(
              'header' => 'Action',
              'index' => 'under_verification',
              'width' => '70px',
              'renderer' => 'Kitnekaa_Shoppinglist_Block_Adminhtml_Editlist_Renderer_Edit'
                      
              ));
      

   
        return parent::_prepareColumns();
    }
  
    //  public function getRowUrl($row)
    // {
      
    //     return $this->getCurrentUrl();
    // }
  
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
      
            $customer_id = $this->getRequest()->getParam('customer_id');
            $list_id =  $this->getRequest()->getParam('list_id');
            
            $formkey = Mage::getSingleton('core/session')->getFormKey();
            $this->setMassactionIdField('item_id');
            $this->getMassactionBlock()->setFormFieldName('item_id');
            $this->getMassactionBlock()->setUseSelectAll(false);
            $ab = Mage::helper("adminhtml")->getUrl("*/shoppinglist/msitemdelete", array('customer_id'=> $customer_id, 'list_id'=>$list_id,'form_key'=> $formkey));
               $this->getMassactionBlock()->addItem('msdelete', array(
            'label'=> 'Delete',
            'url'  => $ab,        
            'confirm' => Mage::helper('shoppinglist')->__('Are you sure want to delete?')
            ));

            return $this;
        }      

  
} 