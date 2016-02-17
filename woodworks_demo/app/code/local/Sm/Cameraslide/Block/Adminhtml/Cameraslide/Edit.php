<?php
/**
 * Created by PhpStorm.
 * User: Vu Van Phan
 * Date: 24/01/2015
 * Time: 00:11
 */
class Sm_Cameraslide_Block_Adminhtml_Cameraslide_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
        $this->_objectId    = 'slide_id';
        $this->_blockGroup  = 'sm_cameraslide';
        $this->_controller  = 'adminhtml_cameraslide';
        $this->_updateButton( 'save', 'label', Mage::helper( 'sm_cameraslide' )->__( 'Save' ) );
        $this->_updateButton( 'delete', 'label', Mage::helper( 'sm_cameraslide' )->__( 'Delete' ) );

        $slide = Mage::registry('slide');
        $privewUrl = $this->getUrl('cameraslide/index/preview', array(
            'id'    => $slide->getId()
        ));
        if($slide->getId())
        {
            $this->_addButton('preview', array(
                'label' => Mage::helper('sm_cameraslide')->__('Preview'),
                'title' => Mage::helper('sm_cameraslide')->__('Preview Slide'),
                'class' => 'show-hide',
                'onclick'   => "popWin('$privewUrl')"
            ));
        }
            $this->addButton('saveandcontinue', array(
                'label'     => Mage::helper('adminhtml')->__('Save and Continue Edit'),
                'onclick'   => 'saveAndContinueEdit()',
                'class'     => 'save'
            ), -100);

        $this->_formScripts[] = "
        editForm = new varienForm('cameraslide_form', '');
                function toggleEditor() {
					if (tinyMCE.getInstanceById('cameraslide_form') == null){
						tinyMCE.execCommand('mceAddControl', false, 'cameraslide_form');
					}else{
						tinyMCE.execCommand('mceRemoveControl', false, 'cameraslide_form');
				    }
				}

				function saveAndContinueEdit(){
					editForm.submit($('cameraslide_form').action+'back/edit/');
				}
        ";
    }

    /*
        Retrieve text for header element depending on loaded page

        @return string
    */
    public function getHeaderText()
    {
        $model = Mage::helper('sm_cameraslide')->getCameraslideItemInstance();
        if($model->getId())
        {
            return "<i class='fa fa-qrcode'></i>".Mage::helper('sm_cameraslide')->__("%s", $this->escapeHtml($model->getData('name_slide')));
        }else{
            return "<i class='fa fa-plus-circle'></i>".Mage::helper('sm_cameraslide')->__('Add New Slide');
        }
    }
}