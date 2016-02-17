<?php
class Sm_Cameraslide_Adminhtml_CameraslideController extends Mage_Adminhtml_Controller_Action{

    /*
     * Retrieve collection table slide
     * */
    public function _getCollectionSlide()
    {
        return 'sm_cameraslide/slide';
    }

    /*
     * Retrieve collection table sliders
     * */
    public function _getCollectionSliders()
    {
        return 'sm_cameraslide/sliders';
    }

    /*
        Init actions
        @return Sm_Cameraslide_Adminhtml_Cameraslide
    */
    public function _initAction()
    {
        // Load layout , set active menu and breadcrumbs
        $this->loadLayout()
            ->_setActiveMenu('sm/cameraslide')
            ->_addBreadcrumb(
                Mage::helper('sm_cameraslide')->__('Slide'),
                Mage::helper('sm_cameraslide')->__('Slide')
            )
            ->_addBreadcrumb(
                Mage::helper('sm_cameraslide')->__('Slide'),
                Mage::helper('sm_cameraslide')->__('Slide')
            );
        return $this;
    }

    public function indexAction(){
        $this->_title($this->__('Cameraslide'))
            ->_title($this->__('Camera Slide'));
        $this->_initAction();
        $block = $this->getLayout()->createBlock('sm_cameraslide/adminhtml_cameraslide', 'cameraslide');
        $this->_addContent($block);
        $this->renderLayout();
    }

    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody( $this->getLayout()->createBlock( 'sm_cameraslide/adminhtml_cameraslide_grid' )->toHtml() );
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $slideId = $this->getRequest()->getParam('id');
        // 1. Instance Manager Slide model
        $model = Mage::getModel($this->_getCollectionSlide())->load($slideId);

        // 2. If ID exists, check it and load data
        if(($model->getId()) || ($slideId == 0))
        {
            // 3. Set entered data if there was an error during save
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if(!empty($data))
            {
                $model->setData($data);
            }
            // 4. Regiter model to use later in blocks
            Mage::register('slide', $model);

            $this->_initAction();
            $this->_title(Mage::helper('sm_cameraslide')->__('Manager Slide'));

            if($model->getId()) {
                $this->_title($model->getData('name_slide'));
            }else{
                $this->_title(Mage::helper('sm_cameraslide')->__('Add New Slide'));
            }
//            $this->loadLayout();
            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()->createBlock('sm_cameraslide/adminhtml_cameraslide_edit'));
            $this->_addLeft($this->getLayout()->createBlock('sm_cameraslide/adminhtml_cameraslide_edit_tabs'));
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);

            // 5. Render Layout
            $this->renderLayout();
        }else{
            $this->_getSession()->addError(Mage::helper('sm_cameraslide')->__('Manager Slide item does not exits'));
            return $this->_redirect('*/*/');
        }
    }

    /*
     * Filter information post of manager slide
     *
     * @return array
     * */
    public function _filterData()
    {
        $slide = $this->getRequest()->getPost();
        if($slide)
        {
            unset($slide['form_key']);
            $data = array(
                'name_slide'            => $slide['name_slide'],
                'status'                => $slide['status'],
                'params'                => Mage::helper('core')->jsonEncode($slide),
            );
        }
        return $data;
    }

    /*
     * Process save information
     * */
    public function saveAction()
    {
        $slideId = $this->getRequest()->getParam('slide_id');
        $filter = $this->_filterData();
        if($filter)
        {
            $slide = Mage::getModel($this->_getCollectionSlide());
            if($slideId)
            {
                $slide->load($slideId);
                $slide->addData($filter);
            }else{
                $slide->setData($filter);
            }
            try{
                $slide->save();
                $this->_getSession()->addSuccess(
                    Mage::helper('sm_cameraslide')->__('The slide item has been saved.')
                );
                $this->_getSession()->setFormData(false);
                if($this->getRequest()->getParam('back'))
                {
                    $this->_redirect('*/*/edit', array(
                        'id' => $slide->getId(),
                        'activeTab' => $this->getRequest()->getParam('activeTab')
                    ));
                    return;
                }
                $this->_redirect( '*/*/index' );
                return;
            }catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }catch (Exception $e){
                $this->_getSession()->addException($e,
                    Mage::helper('sm_cameraslide')->__('An error occurred while saving the slide item.')
                );
            }
        }
        Mage::getSingleton( 'adminhtml/session' )->addError( Mage::helper( 'sm_cameraslide' )->__( 'No data found to save' ) );
        $this->_redirect( '*/*/' );
    }

    /*
     * Delete item choice
     * */
    public function deleteAction()
    {
        $slide_id = $this->getRequest()->getParam('slide_id') ? $this->getRequest()->getParam('slide_id') : array($this->getRequest()->getParam('id'));
        if(is_array($slide_id))
        {
            try{
                $slide = Mage::getModel($this->_getCollectionSlide());
                foreach($slide_id as $s)
                {
                    $slide->load($s);
                    if(!$slide->getId())
                    {
                        Mage::throwException(Mage::helper('sm_cameraslide'))->__('Unable to find a slide');
                    }
                    $slide->delete();
                }
                $this->_getSession()->addSuccess(Mage::helper('sm_cameraslide')->__('The Slide has been delete'));
                $this->_redirect( '*/*/' );

            }catch (Mage_Core_Exception $e){
                $this->_redirectReferer();
                $this->_getSession()->addError($e->getMessage());
            }catch (Exception $e){
                $this->_redirectReferer();
                $this->_getSession()->addException($e,
                    Mage::helper('sm_cameraslide')->__('An error occurred while deleting the slide item.')
                );
            }
        }
    }

    public function slidersAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody($this->getLayout()->createBlock('sm_cameraslide/adminhtml_cameraslide_edit_tab_sliders')->toHtml() );
    }

    public function gridSlidersAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody($this->getLayout()->createBlock('sm_cameraslide/adminhtml_cameraslide_edit_tab_sliders')->toHtml());
    }

    /*
     * Add new sliders for a slide
     * */
    public function addSlidersAction()
    {
        // 1. Instance Manager Slide model
        $modelSlide = Mage::getModel($this->_getCollectionSlide());
        $modelSliders = Mage::getModel($this->_getCollectionSliders());


        // 2. If ID exists, check it and load data
        $slideId = $this->getRequest()->getParam('sid', null);
        $slidersId = $this->getRequest()->getParam('id', null);

        if(is_numeric($slideId))
        {
            $modelSlide->load($slideId);
        }
        if(is_numeric($slidersId))
        {
            $modelSliders->load($slidersId);
        }
        // 4. Register model to use later in blocks
        Mage::register('sliders', $modelSliders);
        Mage::register('slide', $modelSlide);
        $this->_initAction();
        $this->_title(Mage::helper('sm_cameraslide'))->__('Sliders');
        if($modelSliders->getId())
        {
            $this->_title($modelSliders->getData('sliders_title'));
        }else{
            $this->_title(Mage::helper('sm_cameraslide')->__('New Sliders'));
        }

        // Load layout and render layout
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        $this->_addContent($this->getLayout()->createBlock('sm_cameraslide/adminhtml_sliders_edit'));
        $this->_addLeft($this->getLayout()->createBlock('sm_cameraslide/adminhtml_sliders_edit_tabs'));
        $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);

        // 3. Set entered data if there was an error during save
//        $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
//        if(!empty($data))
//        {
//            $modelSliders->addData($data);
//        }

        // 5. Render Layout
        $this->renderLayout();
    }

    public function videoAction()
    {
        $this->loadLayout('overlay_popup');
//        $this->_addContent( $this->getLayout()->createBlock('sm_cameraslide/adminhtml_sliders_video', 'adminhtml_sliders_video'));
        $block = $this->getLayout()->createBlock('sm_cameraslide/adminhtml_sliders_video', 'adminhtml_sliders_video');
        $this->_addContent($block);
        $this->renderLayout();
    }

    public function UploadFile($id)
    {
        $sliders = $this->getRequest()->getPost();
        if(isset($_FILES['image_url']['name']) && ($_FILES['image_url']['tmp_name'] != null))
        {
            try{
                $uploader = new Varien_File_Uploader('image_url');
                $uploader->setAllowCreateFolders(false);
                $uploader->setAllowedExtensions('jpg', 'jpeg', 'gif', 'png');
                $uploader->setAllowRenameFiles(false);
                $uploader->setFilenamesCaseSensitivity(false);
                $uploader->setFilesDispersion(false);
                $path = Mage::getBaseDir('media').DS.'sm'.DS.'cameraslide'.DS.'images'.DS;
                $uploader->save($path, $_FILES['image_url']['name']);

                $modelSliders = Mage::getModel('sm_cameraslide/sliders')->load($id);
                $modelSliders->setData('image_url', $_FILES['image_url']['name']);
                $modelSliders->save();
            }catch (Exception $e){
                $this->_getSession()->addException($e,
                    Mage::helper('sm_cameraslide')->__('An error occurred while upload file sliders.')
                );
            }
        }
    }

    public function _filtetDataSlider()
    {
        $sliders = $this->getRequest()->getPost();

        $slide_id = $sliders['slide_id'];
        $status     = $sliders['status'];
//        $priority   = $sliders['sliders_priority'];
        if($sliders)
        {
            if($sliders['form_key'])
                unset($sliders['form_key']);
            if($sliders['layers'])
            {
                $layers = $sliders['layers'];
                $arraySliders = Mage::helper('core')->jsonDecode($layers);
                unset($sliders['layers']);
//            unset($sliders['slide_id']);
                unset($sliders['sliders_id']);
                unset($sliders['sliders_status']);
                $data = array(
                    'slide_id'  => $slide_id,
                    'status'    => $status,
                    'priority'  => 0,
                    'params'    => Mage::helper('core')->jsonEncode($sliders),
                    'layers'    => Mage::helper('core')->jsonEncode($arraySliders)
                );
            }
        }
        return $data;
    }

    public function saveSlidersAction()
    {
        $slideId    = $this->getRequest()->getParam('slide_id') ? $this->getRequest()->getParam('slide_id') : $this->getRequest()->getParam('sid');
        $slidersId  = $this->getRequest()->getParam('id') ? $this->getRequest()->getParam('id') : $this->getRequest()->getParam('sliders_id');
        $filterData = $this->_filtetDataSlider();
        if($filterData)
        {
            $modelSliders = Mage::getModel($this->_getCollectionSliders());
            if($slidersId)
            {
                $modelSliders->load($slidersId);
                $modelSliders->addData($filterData);
            }else{
                $modelSliders->setData($filterData);
            }
            try{
                $modelSliders->save();
                $this->_getSession()->addSuccess(
                    Mage::helper('sm_cameraslide')->__('The sliders item has been saved.')
                );
                $url = $this->getUrl('*/*/edit', array(
                    'id' => $slideId,
                    'activeTab' => 'form_slide'
                ));
                $this->getResponse()->setBody($url);
                return ;
            }catch (Mage_Core_Exception $e){
                $this->_getSession()->addError($e->getMessage());
            }catch (Exception $e){
                $this->_getSession()->addException($e,
                    Mage::helper('sm_cameraslide')->__('An error occurred while saving the slide item.')
                );
            }
        }
    }

    public function deleteSlidersAction()
    {
        $slide_id = $this->getRequest()->getParam('sid');
        $sliders_id = $this->getRequest()->getParam('id');
        if(is_numeric($sliders_id))
        {
            try{
                $modelSliders = Mage::getModel($this->_getCollectionSliders());
                $modelSliders->load($sliders_id);
                if(!$modelSliders->getId())
                {
                    Mage::throwException(Mage::helper('sm_cameraslide'))->__('Unable to find a sliders');
                }else{
                    $modelSliders->delete();
                }

                $this->_redirect('*/*/edit', array(
                    'id'        => $slide_id,
                    'activeTab' => 'form_slide'
                ));
                $this->_getSession()->addSuccess(Mage::helper('sm_cameraslide')->__('The Sliders has been delete'));
            }catch (Mage_Core_Exception $e){
                $this->_redirect('*/*/edit', array(
                    'id'        => $slide_id,
                    'activeTab' => 'form_slide'
                ));
                $this->_getSession()->addError($e->getMessage());
            }catch (Exception $e){
                $this->_redirect('*/*/edit', array(
                    'id'        => $slide_id,
                    'activeTab' => 'form_slide'
                ));
                $this->_getSession()->addException($e,
                    Mage::helper('sm_cameraslide')->__('An error occurred while deleting the sliders item.')
                );
            }
        }
    }

    public function ajaxSaveAction()
    {
        if($data = $this->getRequest()->getPost())
        {
            $id     = isset($data['entity']) ? (int)$data['entity'] : null;
            $attr   = isset($data['attr']) ? $data['attr'] : null;
            $value  = isset($data['value']) ? (int)$data['value'] : null;
            $out    = array(
                'message' => '',
                'value'   => $value
            );
            switch($attr)
            {
                case 'priority' :
                    $model = Mage::getModel('sm_cameraslide/sliders')->load($id);
                    if($model->getId())
                    {
                        $model->setData($attr, $value);
                        $model->save();
                    }else{
                        $out['message'] = Mage::helper('sm_cameraslide')->__('Sliders not avaiable');
                    }
            }
            $this->getResponse()->setBody(json_encode($out));
        }
    }
}
?>