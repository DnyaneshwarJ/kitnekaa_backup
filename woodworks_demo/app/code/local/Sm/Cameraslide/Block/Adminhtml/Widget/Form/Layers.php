<?php
/**
 * Created by PhpStorm.
 * User: Vu Van Phan
 * Date: 30-01-2015
 * Time: 9:17
 */
class Sm_Cameraslide_Block_Adminhtml_Widget_Form_Layers extends Mage_Adminhtml_Block_Widget implements Varien_Data_Form_Element_Renderer_Interface
{
    protected $_element;
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('sm/cameraslide/widget/form/layers.phtml');
    }

    public function getElement()
    {
        return $this->_element;
    }

    public function setElement(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->_element = $element;
    }

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }

    protected function _prepareLayout()
    {
        $addLayerBtn = $this->getLayout()->createBlock('adminhtml/widget_button', 'addLayerBtn', array(
            'type'      => 'button',
            'label'     => "<i class='fa fa-file-text'></i>".Mage::helper('sm_cameraslide')->__('Add Layer Text'),
            'title'     => Mage::helper('sm_cameraslide')->__('Add Layer Text'),
            'onclick'   => 'CmrSl.addLayerText()',
            'id'        => 'addLayerBtn'
        ));
        $this->setChild('addLayerBtn', $addLayerBtn);

        $addLayerBtn = $this->getLayout()->createBlock('adminhtml/widget_button', 'addLayerImageBtn', array(
            'label'     => "<i class='fa fa-picture-o'></i>".Mage::helper('sm_cameraslide')->__('Add Layer Image'),
            'title'     => Mage::helper('sm_cameraslide')->__('Add Layer Image'),
            'type'      => 'button',
            'onclick'   => sprintf('_MediabrowserUtility.openDialog(\'%s\',\'addLayerImageWindow\', null, null, \'%s\')', Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg_images/index', array(
                'static_urls_allowed'   => 1,
                'onInsertCallback'      => 'CmrSl.addLayerImage'
            )), Mage::helper('sm_cameraslide')->__('Add Image')),
            'id'        => 'addLayerImageBtn'
        ));
        $this->setChild('addLayerImageBtn', $addLayerBtn);

        $addLayerBtn = $this->getLayout()->createBlock( 'adminhtml/widget_button', 'addLayerVideoBtn', array(
            'type' => 'button',
            'title' => Mage::helper( 'sm_cameraslide' )->__( 'Add Layer Video' ),
            'label' => "<i class='fa fa-video-camera'></i>" . Mage::helper( 'sm_cameraslide' )->__( 'Add Layer Video' ),
            'onclick' => sprintf( '_MediabrowserUtility.openDialog(\'%s\', \'addLayerVideoWindow\', null, 700, \'%s\')', Mage::getSingleton( 'adminhtml/url' )->getUrl( 'sm_cameraslide/adminhtml_cameraslide/video' ), Mage::helper( 'sm_cameraslide' )->__( 'Add Video' ) ),
	        'id'        => 'addLayerVideoBtn'
        ) );
        $this->setChild( 'addLayerVideoBtn', $addLayerBtn );

        $addLayerBtn = $this->getLayout()->createBlock('adminhtml/widget_button', 'dupLayerBtn', array(
            'label'     => "<i class='fa fa-files-o'></i>".Mage::helper('sm_cameraslide')->__('Duplicate Layer'),
            'title'     => Mage::helper('sm_cameraslide')->__('Duplicate Layer'),
            'onclick'   => 'CmrSl.duplicateLayer()',
            'type'      => 'button',
            'id'        => 'dupLayerBtn'
        ));
        $this->setChild('dupLayerBtn', $addLayerBtn);

        $addLayerBtn = $this->getLayout()->createBlock('adminhtml/widget_button', 'editLayerBtn', array(
            'title'     => Mage::helper('sm_cameraslide')->__('Edit Layer'),
            'label'     => "<i class='fa fa-pencil-square-o'></i>".Mage::helper('sm_cameraslide')->__('Edit Layer'),
            'onclick'   => 'CmrSl.editLayer()',
            'type'      => 'button',
            'id'        => 'editLayerBtn'
        ));
        $this->setChild('editLayerBtn', $addLayerBtn);

        $addLayerBtn = $this->getLayout()->createBlock('adminhtml/widget_button', 'deleteLayerBtn', array(
            'label'     => "<i class='fa fa-times-circle'></i>".Mage::helper('sm_cameraslide')->__('Delete Layer'),
            'title'     => Mage::helper('sm_cameraslide')->__('Delete Layer'),
            'type'      => 'button',
            'onclick'   => 'CmrSl.deleteLayer()',
            'id'        => 'deleteLayerBtn'
        ));
        $this->setChild('deleteLayerBtn', $addLayerBtn);

        $addLayerBtn = $this->getLayout()->createBlock('adminhtml/widget_button', 'deleteAllLayerBtn', array(
            'label'     => "<i class='fa fa-times-circle'></i>".Mage::helper('sm_cameraslide')->__('Delete All Layer'),
            'title'     => Mage::helper('sm_cameraslide')->__('Delete All Layer'),
            'type'      => 'button',
            'onclick'   => 'CmrSl.deleteAllLayers()',
            'id'        => 'deleteAllLayerBtn'
        ));
        $this->setChild('deleteAllLayerBtn', $addLayerBtn);
        return parent::_prepareLayout();
    }

    public function getDivLayersStyle()
    {
        $slide = Mage::registry('slide');
        if($slide->getId())
        {
            return sprintf('width:%s; height:%s;', $slide->getData('slide_width') ? $slide->getData('slide_width').'px' : '960px', $slide->getData('slide_height') ? $slide->getData('slide_height').'px' : '574px');
        }
    }

    public function getAddLayerImageUrl()
    {
        return Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg_images/index', array(
            'static_urls_allowed'   => 1,
            'onInsertCallback'      => 'CmrSl.addLayerImage'
        ));
    }

	public function getAddLayerVideoUrl()
	{
		return Mage::getSingleton( 'adminhtml/url' )->getUrl( 'sm_cameraslide/adminhtml_cameraslide/video' );
	}
}