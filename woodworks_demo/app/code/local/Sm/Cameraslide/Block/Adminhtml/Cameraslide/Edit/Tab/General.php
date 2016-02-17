<?php
    class Sm_Cameraslide_Block_Adminhtml_Cameraslide_Edit_Tab_General extends Mage_Adminhtml_Block_Widget_Form
    {
        protected function _prepareLayout()
        {
            $head = $this->getLayout()->getBlock('head');
            if((Mage::app()->getRequest()->getActionName() == 'edit') || (Mage::app()->getRequest()->getActionName() == 'new'))
            {
                $head->addCss('prototype/windows/themes/default.css');
                $head->addCss('prototype/windows/themes/magento.css');
                $head->addCss('lib/prototype/windows/themes/magento.css');
                $head->addJs('lib/flex.js');
                $head->addJs('lib/FABridge.js');
                $head->addJs('mage/adminhtml/flexuploader.js');
                $head->addJs('sm/cameraslide/js/renderhelper.js');
            }
            $return = parent::_prepareLayout();
            if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
                $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
            }
            return $return;
        }
        /*
         *  Prepare form elements
         *
         *  @return Mage_Adminhtml_Block_Widget_Form
         * */
        public function _prepareForm()
        {
            $model = Mage::registry('slide');
            /*
             * Checking id use have permissions to save information
             * */
            if(Mage::helper('sm_cameraslide/admin')->isActionAllowed('save'))
            {
                $isElementDisabled = false;
            }else{
                $isElementDisabled = true;
            }
            $form = new Varien_Data_Form();
            $form->setHtmlIdPrefix('cameraslide_general_');

            $fieldset = $form->addFieldset('base_fieldset', array(
                'legend'    => "<i class='fa fa-gears'></i>".Mage::helper('sm_cameraslide')->__('General Options'),
                'class'     => 'collapsible'
            ));

            if($model->getId())
            {
                $fieldset->addField('slide_id', 'hidden', array(
                    'name'  => 'slide_id'
                ));
            }

            $fieldset->addField('name_slide', 'text', array(
                'name'      => 'name_slide',
                'label'     => Mage::helper('sm_cameraslide')->__('Name Slide'),
                'title'     => Mage::helper('sm_cameraslide')->__('Name Slide'),
                'class'     => 'required-entry',
                'required'  => true,
                'disable'   => $isElementDisabled
            ));

            $fieldset->addField('status', 'select', array(
                'name'      => 'status',
                'label'     => Mage::helper('sm_cameraslide')->__('Status'),
                'title'     => Mage::helper('sm_cameraslide')->__('Status'),
                'require'   => false,
                'type'      => 'options',
                'options'   => $model->getOptionStatus(),
                'disable'   => $isElementDisabled
            ));

            $fieldset->addField('slide_width', 'text', array(
                'class'     => 'required-entry validate-number',
                'name'      => 'slide_width',
                'label'     => Mage::helper('sm_cameraslide')->__('Slide Width'),
                'title'     => Mage::helper('sm_cameraslide')->__('Slide Width'),
                'require'   => true,
                'value'     => $model->getData('slide_width') ? $model->getData('slide_width') : '960',
                'note'      => 'The width of the slide you want, here only use type "px"',
                'disable'   => $isElementDisabled
            ));

            $fieldset->addField('slide_height', 'text', array(
                'class'     => 'required-entry validate-number',
                'name'      => 'slide_height',
                'label'     => Mage::helper('sm_cameraslide')->__('Slide Height'),
                'title'     => Mage::helper('sm_cameraslide')->__('Slide Height'),
                'require'   => true,
                'value'     => $model->getData('slide_height') ? $model->getData('slide_height') : '574',
                'disable'   => $isElementDisabled,
	            'note'      => 'The height of slide you want, here only use type "px"'
            ));

            $fieldset->addField('time_load', 'text', array(
                'class'     => 'validate-number required-entry',
                'name'      => 'time_load',
                'label'     => Mage::helper('sm_cameraslide')->__('Time Load'),
                'title'     => Mage::helper('sm_cameraslide')->__('Time Load'),
                'require'   => true,
                'note'      => Mage::helper('sm_cameraslide')->__('Milliseconds between the end of the sliding effect and start of the next one'),
                'value'     => (int)$model->getData('time_load') ? (int)$model->getData('time_load') : 7000,
                'disable'   => $isElementDisabled
            ));

            $fieldset->addField('trans_period', 'text', array(
                'class'     => 'validate-number required-entry',
                'name'      => 'trans_period',
                'label'     => Mage::helper('sm_cameraslide')->__('Translate Period'),
                'title'     => Mage::helper('sm_cameraslide')->__('Translate Period'),
                'require'   => true,
                'value'     => (int)$model->getData('time_load') ? (int)$model->getData('time_load') : 1500,
                'note'      => Mage::helper('sm_cameraslide')->__('Length of the sliding effect in milliseconds'),
                'disable'   => $isElementDisabled
            ));

            $fieldset->addField('play_pause', 'select', array(
                'name'      => 'play_pause',
                'label'     => Mage::helper('sm_cameraslide')->__('Play Pause'),
                'title'     => Mage::helper('sm_cameraslide')->__('Play Pause'),
                'require'   => false,
                'type'      => 'options',
                'options'   => $model->getTrueFalse(),
                'note'      => 'To display or not the play/pause buttons',
                'disable'   => $isElementDisabled
            ));

            $fieldset->addField('auto_advance', 'select', array(
                'name'      => 'auto_advance',
                'label'     => Mage::helper('sm_cameraslide')->__('Auto Advance'),
                'title'     => Mage::helper('sm_cameraslide')->__('Auto Advance'),
                'require'   => false,
                'type'      => 'options',
                'options'   => $model->getTrueFalse(),
                'note'      => 'Use or not auto transitions slide',
                'disable'   => $isElementDisabled
            ));

            $fieldset->addField('prev_next', 'select', array(
                'name'      => 'prev_next',
                'label'     => Mage::helper('sm_cameraslide')->__('Prev Next'),
                'title'     => Mage::helper('sm_cameraslide')->__('Prev Next'),
                'require'   => false,
                'type'      => 'options',
                'options'   => $model->getTrueFalse(),
                'note'      => 'To display or not the prev / next buttons',
                'disable'   => $isElementDisabled
            ));

            $fieldset->addField('pagination', 'select', array(
                'name'      => 'pagination',
                'label'     => Mage::helper('sm_cameraslide')->__('Pagination'),
                'title'     => Mage::helper('sm_cameraslide')->__('Pagination'),
                'require'   => false,
                'values'    => $model->getTrueFalse(),
                'note'      => 'To display or not the pagination buttons',
                'disable'   => $isElementDisabled
            ));


            Mage::dispatchEvent('adminhtml_cameraslide_edit_tab_slide_prepare_form', array('form' => $form));
            /*
             * Điều kiện if sẽ phân biệt trường hợp add và edit là khác nhau
             *
             * + nếu có Id thì sẽ setValues với values của id đó.
             * + nếu ko có Id thì sẽ setValues với values mặc định của addField
             * */
            if($model->getId()){
                $form->setValues($model->getData());
            }
            $this->setForm($form);
            return parent::_prepareForm();
        }
    }
?>