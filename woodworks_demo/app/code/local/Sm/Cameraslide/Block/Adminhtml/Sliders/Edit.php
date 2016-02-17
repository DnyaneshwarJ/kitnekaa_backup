<?php
	/**
	* 
	*/
	class Sm_Cameraslide_Block_Adminhtml_Sliders_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
	{
		public function __construct()
		{
			$this->_blockGroup      = 'sm_cameraslide';
            $this->_controller      = 'adminhtml_sliders';
            $this->_form            = 'edit';
            $slide                  = Mage::registry('slide');
            $sliders                = Mage::registry('sliders');
            $mediaUrl               = Mage::getBaseUrl('media');

            $this->_formScripts[]   = "editForm = new varienForm('sliders_form', '');";
            $this->_formScripts[]   = "var CmrSl = new CameraSlide(editForm, {$slide->getData('time_load')}, {media_url: '{$mediaUrl}'});";


            if(is_array($sliders->getLayers()))
            {
                foreach ($sliders->getLayers() as $layer) {
                    $this->_formScripts[] = "CmrSl.addLayer(".Mage::helper('core')->jsonEncode($layer).");";
                }
            }
            parent::__construct();
		}

        public function _prepareLayout()
        {
            parent::_prepareLayout();
            $slide  = Mage::registry('slide');
            $sliders = Mage::registry('sliders');
            $backUrl = $this->getUrl('*/*/edit', array(
                'id'        => $slide->getId(),
                'activeTab' => 'form_slide' // name tab lấy ở trong Sm_Cameraslide_Block_Adminhtml_Cameraslide_Edit_Tabs tab Sliders
            ));

            $deleteUrl = $this->getUrl('*/*/deleteSliders', array(
                'id'    => $sliders->getId(),
                'sid'   => $slide->getId()
            ));
            $this->updateButton('delete', 'onclick', "setLocation('{$deleteUrl}');");
            $this->updateButton('save', 'onclick', 'CmrSl.save();');
            $this->updateButton('back', 'onclick', "setLocation('{$backUrl}');");
            $this->_addButton('sac', array(
                'label'     => Mage::helper('sm_cameraslide')->__('Save And Continue Edit'),
                'class'     => 'save',
                'onclick'   => 'CmrSl.save(true);'
            ));
            return $this;
        }

        public function getIdSlide(){
            $slideId = $this->getRequest()->getParam('sid');
            if(is_numeric($slideId))
            {
                return $this->getRequest()->getParam('sid');
            }
        }

        /*
        Retrieve text for header element depending on loaded page

        @return string
        */
        public function getHeaderText()
        {
            $modelSliders = Mage::registry('sliders');//helper('sm_cameraslide')->getCameraslideItemInstance();
//            $params = Mage::helper('core')->jsonDecode($modelSliders['params']);
            if($modelSliders->getId())
            {
                return "<i class='fa fa-qrcode'></i>".Mage::helper('sm_cameraslide')->__("%s", $this->escapeHtml($modelSliders->getData('sliders_title')));
            }else{
                return "<i class='fa fa-plus-circle'></i>".Mage::helper('sm_cameraslide')->__('Add New Sliders');
            }
        }
	}
?>