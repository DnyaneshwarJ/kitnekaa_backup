<?php
	/**
	* 
	*/
	class Sm_Cameraslide_Block_Adminhtml_Cameraslide_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
	{
		
		public function __construct()
		{
			parent::__construct();
			$this->setId('page_tabs');
			$this->setDestElementId('cameraslide_form');
            if ( $tab = $this->getRequest()->getParam( 'activeTab' ) )
            {
                $this->_activeTab = $tab;
            }
            else
            {
                $this->_activeTab = 'form_general';
            }
			$this->setTitle("<i class='fa fa-windows'></i>".Mage::helper('sm_cameraslide')->__('Manager Slide'));
		}

		protected function _beforeToHtml()
		{
			$this->addTab('form_general', array(
				'label' 	=> "<i class='fa fa-gears'></i>".Mage::helper('sm_cameraslide')->__('General Options'),
				'title' 	=> Mage::helper('sm_cameraslide')->__('General Options'),
				'content'	=> $this->_getTabHtml('general'),
			));

            $this->addTab('form_slide', array(
				'label' 	=> "<i class='fa fa-picture-o'></i>" . Mage::helper('sm_cameraslide')->__('Sliders'),
				'title'		=> Mage::helper('sm_cameraslide')->__('Sliders'),
                'url' => $this->getUrl( '*/*/sliders', array(
                    '_current' => true
                ) ),
                'class' => 'ajax'
			));

            return parent::_beforeToHtml();
		}

        protected function _getTabHtml( $tab )
        {
            return $this->getLayout()->createBlock( 'sm_cameraslide/adminhtml_cameraslide_edit_tab_' . $tab )->toHtml();
        }
	}
?>