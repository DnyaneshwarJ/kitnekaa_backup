


 <?php 
 class Kitnekaa_Credittransfer_Block_Adminhtml_Credittransfer_Grid extends Mage_Adminhtml_Block_Widget_Grid
 {
      public function __construct()
    {  
      
        parent::__construct();
        $this->setId('verifying_company_id');
        $this->setUseAjax(true);
         $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setFilterVisibility(false);
       
    }
  
 protected function _getCollectionClass()
    {
        return 'credittransfer/verifyingcompany';
    }
  
   protected function _prepareCollection()
    {
        $customer_id = Mage::helper('credittransfer')->findcompany();
         $select = Mage::getModel('credittransfer/verifyingcompany')->getCollection();
         $mod  = Mage::getSingleton('core/resource')->getTableName('credittransfer/docneeded');
         $mod2 = Mage::getSingleton('core/resource')->getTableName('credittransfer/docs');   
    
           
             $select->getSelect()->joinLeft(array('b'=>$mod),'main_table.verifying_company_id=b.verifying_company_id', array('b.doc_id'));
             $select->getSelect()->joinLeft(array('c'=>$mod2),'b.verifying_company_id = c.verifying_company_id AND b.doc_id = c.doc_id AND c.under_verification = 1 and c.customer_id='.$customer_id, 
             array('c.doc_path','c.company_id','c.under_verification','c.customer_id'));
             $select->getSelect()->group('main_table.verifying_company_id');
            //echo $select->getSelect();
            $this->setCollection($select);

        return parent::_prepareCollection();
         
    }
    protected function _prepareColumns()
    {       

         $payments = Mage::getSingleton('payment/config')->getActiveMethods();

        $methods = array();
        foreach ($payments as $paymentCode=>$paymentModel)
        {
                $paymentTitle = Mage::getStoreConfig('payment/'.$paymentCode.'/title');
                $methods[$paymentCode] = $paymentTitle;
        }

          $this->addColumn('verifying_company_id', array(
          'header'    => 'ID',
          'align'     =>'right',
          'width'     => '10px',
          'index'     => 'verifying_company_id',
        ));





         $this->addColumn('verifying_company_name', array(
            'header' => 'Financer',
            'index' => 'verifying_company_name',
            'width' => '100px',
            'filter_index' => 'verifying_company_name'
        ));
        
 
  


        $this->addColumn('under_verification', array(
                        'header' => 'Verification Require',
                        'index' => 'under_verification',
                        'width' => '70px',
                        //'type' => 'option',
                        'renderer' => 'Kitnekaa_Credittransfer_Block_Adminhtml_Credittransfer_Renderer_Verification'
                        
                ));

     
        $this->addColumn('sendmail', array(
            'header'    => 'Send Mail',
            'align'     => 'left',
            'width'     => '40px',
            //'index'     => 'sendmail',
             'renderer' => 'Kitnekaa_Credittransfer_Block_Adminhtml_Credittransfer_Renderer_Sendmail'
                   ));
  
         $this->addColumn('view', array(
            'header'    => 'View',
            'align'     => 'left',
            'width'     => '40px',
            'index'     => 'sendmail',
             'renderer' => 'Kitnekaa_Credittransfer_Block_Adminhtml_Credittransfer_Renderer_Viewdocs',
             
                   ));




   
        return parent::_prepareColumns();
    }
  
     public function getRowUrl($row)
    {
      //return $this->getUrl('*/credittransfer/edit', array('id' => $row->getVerifyingCompanyId(), 'maincompany'=> $row->getCompanyId()));
        
    }
  
    // public function getGridUrl()
    // {   return $this->getUrl('*/credittransfer/grid', array('_current' => true));
      
    // }
    
       protected function _getSession()
        {
            return Mage::getSingleton('customer/session');
        }

      

  
} 