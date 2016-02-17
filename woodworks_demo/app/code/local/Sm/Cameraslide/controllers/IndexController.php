<?php
class Sm_Cameraslide_IndexController extends Mage_Core_Controller_Front_Action{

    public function indexAction(){
        $this->loadLayout();
        $this->getLayout()->getBlock('root')->setTemplate('sm/cameraslide/cameraslide.phtml');
        $this->renderLayout();
    }

    public function previewAction()
    {
        $id = $this->getRequest()->getParam('id');
        $this->loadLayout();
        $this->getLayout()->getBlock('root')->setTemplate('page/empty.phtml');
        $block = $this->getLayout()->createBlock('sm_cameraslide/slide_preview', '', array(
            'id'    => $id
        ));
        $this->getLayout()->getBlock('content')->append($block);
        $this->_title(Mage::helper('sm_cameraslide')->__('Sm Camere SlideShow'))
            ->_title(Mage::helper('sm_cameraslide')->__('Preview Slide'));
        $this->renderLayout();
    }
}
?>