<?php
/**
 * Created by PhpStorm.
 * User: Vu Van Phan
 * Date: 15-05-2015
 * Time: 16:19
 */
class Sm_Cameraslide_Block_Adminhtml_Sliders_Video extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_blockGroup  = 'sm_cameraslide';
        $this->_controller  = 'adminhtml_sliders';
        $this->_mode        = 'video';
        parent::__construct();
        $this->setId('addVideoForm');
        $this->_headerText = Mage::helper('sm_cameraslide')->__('Add Video Form');
        $this->removeButton('back');
        $this->removeButton('reset');
        $popupId = $this->getRequest()->getParam('popupId');
        $this->_updateButton('save', 'onclick', "CmrSl.addLayerVideo('{$popupId}')");
        if($serial = $this->getRequest()->getParam('serial'))
        {
            $this->_formScripts[] = "CmrSl.assignVideoForm('{$serial}')";
	        $this->_updateButton('save', 'label', Mage::helper('sm_cameraslide')->__('Update Video'));
        }
        else
        {
            $this->_formScripts[] = 'CmrSl.toggleVideoForm(false)';
	        $this->_updateButton('save', 'label', Mage::helper('sm_cameraslide')->__('Add Video'));
        }
    }
}