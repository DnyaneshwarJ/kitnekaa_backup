


 <?php 
 class Kitnekaa_Shoppinglist_Block_Adminhtml_Modifylist_Grid extends Mage_Adminhtml_Block_Widget_Grid
 { 
  protected $customer_id;
      public function __construct()
    {  
      
        parent::__construct();
        $this->setId('shoppinglist_id');
        $this->setUseAjax(true);
         $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setFilterVisibility(false);
        $this->customer_id = $this->getRequest()->getParam('id');    
    }
  
 protected function _getCollectionClass()
    {
        return 'credittransfer/verifyingcompany';
    }
  
   protected function _prepareCollection()
    {
        $company_id = Mage::helper('shoppinglist')->findcompany();
         $select = Mage::getModel('shoppinglist/shoppinglist')->getCollection();
         $select->addFieldToFilter('main_table.company_id',array('eq'=>  $company_id ));
        
             
             $select->getSelect();
            
            $this->setCollection($select);

        return parent::_prepareCollection();
         
    }
    protected function _prepareColumns()
    {       

        $this->addColumn('list_id', array(
          'header'    => 'ID',
          'align'     =>'left',
          'width'     => '10px',
          'index'     => 'list_id',
        ));

         $this->addColumn('list_name', array(
            'header' => 'List Name',
            'align'     =>'left',
            'index' => 'list_name',
            'width' => '100px',
            'filter_index' => 'list_name'
        ));
        
        $this->addColumn('status', array(
            'header' => Mage::helper('shoppinglist')->__('Action'),
             'align'     =>'left',
            'width' => '100',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => Mage::helper('shoppinglist')->__('Edit'),
                    'url' => array('base' => '*/shoppinglist/edit/customer_id/'.$this->customer_id),
                    'field' => 'list_id'
                ),
            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'status',
            'is_system' => true,
        ));
  

        return parent::_prepareColumns();
    }
  
     public function getRowUrl($row)
    {
      return $this->getCurrentUrl();
    }
  
    public function getGridUrl()
    {   return $this->getCurrentUrl();
    }
    
       protected function _getSession()
        {
            return Mage::getSingleton('customer/session');
        }

       public function getMainButtonsHtml()
          {  
          $customer_id = $this->getRequest()->getParam('id');
          $html = parent::getMainButtonsHtml();      //get the parent class buttons
          $addButton = $this->getLayout()->createBlock('adminhtml/widget_button') //create the add button
                 ->setData(array(
                        'label'     => Mage::helper('adminhtml')->__('Add Shoppinglist'),
                        'onclick'   => "setLocation('".$this->getUrl('*/shoppinglist/addshoplist',array('customer_id' => $customer_id))."')",
                        'class'   => 'task'
                   ))->toHtml();
            return $addButton.$html;
         }

           protected function _prepareMassaction()
        {
            $customer_id = $this->getRequest()->getParam('id');
            
            $formkey = Mage::getSingleton('core/session')->getFormKey();
            $this->setMassactionIdField('list_id');
            $this->getMassactionBlock()->setFormFieldName('list_id');
            
            $ab = Mage::helper("adminhtml")->getUrl("*/shoppinglist/deleteshoplist", array('customer_id'=> $customer_id,'form_key'=> $formkey));
            
            $this->getMassactionBlock()->addItem('msdelete', array(
            'label'=> 'Delete',
            'url'  => $ab,        
            'confirm' => Mage::helper('shoppinglist')->__('Are you sure want to delete?')
            ));

            return $this;
        }  

  
} 