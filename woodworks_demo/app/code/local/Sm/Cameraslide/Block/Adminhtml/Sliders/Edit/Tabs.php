<?php
/**
 * Created by PhpStorm.
 * User: Vu Van Phan
 * Date: 28-01-2015
 * Time: 16:44
 */
class Sm_Cameraslide_Block_Adminhtml_Sliders_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('sliders_tabs');
        $this->setDestElementId('sliders_form');
        $slide = Mage::registry('slide');

        if($slide->getId())
        {
            $this->setTitle("<i class='fa fa-windows'></i>".Mage::helper('sm_cameraslide')->__('%s\'s Sliders', $slide->getData('name_slide')));
        }else{
            $this->setTitle(Mage::helper('sm_cameraslide')->__('Sliders'));
        }
    }

    public function _prepareLayout()
    {

        $modelSlide     = Mage::registry('slide');
        $modelSliders   = Mage::registry('sliders');
        $_sliders       = $modelSlide->getAllSliders();
        foreach($_sliders as $item)
        {
            if(($item->getId()) == ($modelSliders->getId()))
            {
                $this->addTab('sliders_section_'.$item->getId(), array(
                    'title'     => $item->getData('sliders_title') ? $item->getData('sliders_title') : "Sliders : {$item->getData('sliders_title')}",
                    'label'     => $item->getData('sliders_title') ? "<i class='fa fa-th-list'></i>".$item->getData('sliders_title') : "Sliders : {$item->getData('sliders_title')}",
                    'content'   => $this->getLayout()->createBlock('sm_cameraslide/adminhtml_sliders_edit_tab_main')->toHtml()
                ));
                $this->_activeTab = 'sliders_section_'.$item->getId();
            }else{
                $this->addTab('sliders_section_'.$item->getId(), array(
                    'title'     => $item->getData('sliders_title') ? $item->getData('sliders_title') : "Sliders : {$item->getData('sliders_title')}",
                    'label'     => $item->getData('sliders_title') ? "<i class='fa fa-th-list'></i>".$item->getData('sliders_title') : "Sliders : {$item->getData('sliders_title')}",
                    'url'       => $this->getUrl('*/*/addSliders', array(
                        'sid'   => $modelSlide->getId(),
                        'id'    => $item->getId()
                    ))
                ));
            }
        }
        if(!$modelSliders->getId())
        {
            $this->addTab('sliders_section_new', array(
                'title'     => Mage::helper('sm_cameraslide')->__('New Sliders'),
                'label'     => "<i class='fa fa-puzzle-piece'></i>".Mage::helper('sm_cameraslide')->__('New Sliders'),
                'content'   => $this->getLayout()->createBlock('sm_cameraslide/adminhtml_sliders_edit_tab_main')->toHtml()
            ));
            $this->_activeTab = 'sliders_section_new';
        }else{
            $this->addTab('sliders_section_new', array(
                'title'     => Mage::helper('sm_cameraslide')->__('New Sliders'),
                'label'     => "<i class='fa fa-puzzle-piece'></i>".Mage::helper('sm_cameraslide')->__('New Sliders'),
                'url'       => $this->getUrl('*/*/addSliders', array(
                    'sid'   => $modelSlide->getId(),
                ))
            ));
        }
        parent::_prepareLayout();
    }
}