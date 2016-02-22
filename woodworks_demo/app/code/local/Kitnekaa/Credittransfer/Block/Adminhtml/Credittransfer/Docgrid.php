 <?php 
 class Kitnekaa_Credittransfer_Block_Adminhtml_Credittransfer_Docgrid extends Mage_Adminhtml_Block_Widget_Grid
 {
      public function __construct()
    {
        parent::__construct();
        $this->setId('company_id');
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
         $financer = Mage::registry('financer');
         $customer_id = Mage::registry('company');
         $select = Mage::getModel('credittransfer/docs')->getCollection();
         $mod  = Mage::getSingleton('core/resource')->getTableName('credittransfer/docname');
         $select->getSelect()->joinLeft(array('b'=>$mod),'main_table.doc_id=b.doc_id', array('b.doc_name'));
         $select->addFieldToFilter('main_table.verifying_company_id',array('eq'=> $financer));
         $select->addFieldToFilter('main_table.customer_id',array('eq'=> $customer_id));
         
            
            $this->setCollection($select);

        return parent::_prepareCollection();
         
    }
    protected function _prepareColumns()
    {        
        $link = Mage::helper('adminhtml')->getUrl('adminhtml/credittransfer/delete', array('id' => '$id'));
        


        $img = "<img src='".Mage::getBaseUrl('skin')."adminhtml/base/default/images/crmaddon/ico_success.gif' width='10' height='10'/>";
       

          $this->addColumn('id', array(
          'header'    => 'ID',
          'align'     =>'right',
          'width'     => '10px',
          'index'     => 'id',
        ));

         $this->addColumn('doc_path', array(
            'header' => 'Doc Path',
            'index' => 'doc_path',
            'width' => '100px',
           
        ));
      
        $this->addColumn('doc_name', array(
                        'header' => 'Document Name',
                        'index' => 'doc_name',
                        'width' => '70px',
                                          
                ));
 
        $this->addColumn('delete', array(
        'header'   => 'Delete',
        'width'    => '15',
        'index'  => 'id',
        //'type'     => 'action',
        'renderer' => 'Kitnekaa_Credittransfer_Block_Adminhtml_Credittransfer_Renderer_Deletedocs'
         
             ));

        $this->addColumn('show', array(
            'header'    =>  'Show',
            'align'     => 'left',
            'width'     => '120px',
            'type'      => 'action',
            'actions' => array(
                array(
                    'url' => $showurl,
                'caption' => 'Detail',
                'onclick' =>"return popitup('".$showurl."')"
                 ),
                ),
           'renderer' => 'Kitnekaa_Credittransfer_Block_Adminhtml_Credittransfer_Renderer_Showaction' 

        ));  
  
  
        $this->addColumn('verified', array(
  
            'header'    => 'status',
            'align'     => 'left',
            'width'     => '80px',
            'index'     => 'verified',
            'type'      => 'options',
            'renderer' => 'Kitnekaa_Credittransfer_Block_Adminhtml_Credittransfer_Renderer_Statuses',
            'options'   => array(
                1 => 'Verified',
                0 => 'Not Verified'
            ),
        ));
  
        return parent::_prepareColumns();
    }
  
     public function getRowUrl($row)
    {
      //return $this->getUrl('*/credittransfer/edit', array('id' => $row->getVerifyingCompanyId(), 'maincompany'=> $row->getCompanyId()));
        
    }
  
    public function getGridUrl()
    {  // return $this->getUrl('*/credittransfer/', array('_current' => true));
      
    }
    
       protected function _getSession()
        {
            return Mage::getSingleton('customer/session');
        }

protected function _prepareMassaction()
{
    $formkey = Mage::getSingleton('core/session')->getFormKey();
$this->setMassactionIdField('id');
$this->getMassactionBlock()->setFormFieldName('docid');
 $ab = Mage::helper("adminhtml")->getUrl("*/credittransfer/msdelete");
 //$customer_id = 
$this->getMassactionBlock()->addItem('delete', array(
'label'=> 'Set Verified',
'url'  => $ab.'?form_key='.$formkey,        
'confirm' => Mage::helper('tax')->__('Are you sure?')
));
 
return $this;
}

    public function getMainButtonsHtml()
          {   $financer = Mage::registry('financer');
         $customer_id = Mage::registry('company');
         $verifyingname = Mage::registry('verifyingname');
             $html = parent::getMainButtonsHtml();          //get the parent class buttons
             $addButton = $this->getLayout()->createBlock('adminhtml/widget_button') //create the add button
                 ->setData(array(
                        'label'     => Mage::helper('adminhtml')->__('Upload'),
                        'onclick'   => "setLocation('".$this->getUrl('*/credittransfer/adddocs',array('financer' => $financer, 'company'=> $customer_id,'verifyingname'=> $verifyingname))."')",
                        'class'   => 'task'
                   ))->toHtml();
    return $addButton.$html;
}

} 