<?php
/**
 * Created by PhpStorm.
 * User: Vu Van Phan
 * Date: 28-01-2015
 * Time: 17:15
 */
class Sm_Cameraslide_Block_Adminhtml_Sliders_Edit_Tab_Main extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareLayout()
    {
        $head = $this->getLayout()->getBlock('head');
        if(Mage::app()->getRequest()->getActionName() == 'addSliders')
        {
            $head->addCss('lib/prototype/windows/themes/magento.css');
            $head->addJs('lib/flex.js');
            $head->addJs('lib/FABridge.js');
            $head->addJs('mage/adminhtml/flexuploader.js');
            $head->addJs('sm/cameraslide/js/renderhelper.js');
            $head->addJs('sm/cameraslide/js/rendersliders.js');
            $head->addJs('sm/cameraslide/js/jquery-2.1.3.min.js');
            $head->addJs('sm/cameraslide/js/jquery-migrate-1.2.1.min.js');
            $head->addJs('sm/cameraslide/js/jquery-noconflict.js');
            $head->addJs('sm/cameraslide/js_plugin/js/camera.js');
            $head->addItem('skin_css', 'sm/cameraslide/css/cameraslide.css');
            $head->addItem('skin_css', 'sm/cameraslide/css/jquery-ui-1.10.3.min.css');
            $head->addItem('skin_css', 'sm/cameraslide/css/font-awesome.css');
        }
        $return = parent::_prepareLayout();
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
        return $return;
    }
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
    public function getIdSlide()
    {
        $slideId = $this->getRequest()->getParam('sid');
        if(is_numeric($slideId))
        {
            return $slideId;
        }
    }

    public function getIdSliders()
    {
        $slidersId = $this->getRequest()->getParam('id');
        if(is_numeric($slidersId))
        {
            return $slidersId;
        }else{
            return '';
        }
    }
    /*
     *  Prepare form elements
     *
     *  @return Mage_Adminhtml_Block_Widget_Form
     * */
    public function _prepareForm()
    {
        $modelSlide   = Mage::registry('slide');
        $modelSliders = Mage::registry('sliders');

        $form = new Varien_Data_Form();
        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend'    => "<i class='fa fa-cog'></i>".Mage::helper('sm_cameraslide')->__('General'),
            'class'     => 'collapsible'
        ));

        if($modelSlide->getId())
        {
            $fieldset->addField('slide_id', 'hidden', array(
                'name'  => 'slide_id',
                'value' => $modelSlide->getId(),
            ));
        }

        if($modelSliders->getId())
        {
            $fieldset->addField('sliders_id', 'hidden', array(
                'name'  => 'sliders_id',
                'value' => $modelSliders->getId()
            ));
        }

        if($modelSliders->getData('priority'))
        {
            $fieldset->addField('priority', 'hidden', array(
                'name'  => 'sliders_priority',
                'value' => $modelSliders->getData('priority')
            ));
        }
        $fieldset->addField('sliders_title', 'text', array(
            'name'      => 'sliders_title',
            'label'     => Mage::helper('sm_cameraslide')->__('Title'),
            'title'     => Mage::helper('sm_cameraslide')->__('Title'),
            'class'     => 'required-entry',
            'required'  => true,
        ));

        $fieldset->addField('status', 'select', array(
            'name'      => 'status',
            'label'     => Mage::helper('sm_cameraslide')->__('Status'),
            'title'     => Mage::helper('sm_cameraslide')->__('Status'),
            'values'    => $modelSliders->getStatusActiveNotActive(),
            'required'  => false,
        ));

        $fieldset2 = $form->addFieldset('layers_fieldset', array(
            'legend'    => "<i class='fa fa-tasks'></i>".Mage::helper('sm_cameraslide')->__('Sliders Image and Layers'),
            'class'     => 'collapsible'
        ));

        $bg = $fieldset2->addField('background_type', 'select', array(
            'name'      => 'background_type',
            'label'     => Mage::helper('sm_cameraslide')->__('Background Type'),
            'title'     => Mage::helper('sm_cameraslide')->__('Background Type'),
            'values'    => $modelSliders->getBackgroundType(),
            'value'     => $modelSliders->getBackgroundType() ? $modelSliders->getBackgroundType() : 'image',
            'onchange'  => 'CmrSl.updateContainer()',
            'required'  => false,
        ));

        $bg1 = $fieldset2->addField('data_src', 'text', array(
            'name'  => 'data_src',
            'label' => Mage::helper('sm_cameraslide')->__('Data Src Image'),
            'title' => Mage::helper('sm_cameraslide')->__('Data Src Image'),
            'onchange'  => 'CmrSl.updateContainer()',
            'note'  => Mage::helper('sm_cameraslide')->__('The URL of the image of the slide')
        ));
        $form->getElement('data_src')->setRenderer($this->getLayout()->createBlock('sm_cameraslide/adminhtml_widget_form_browsers', '', array(
            'element'   => $bg1
        )));

        $bg6 = $fieldset2->addField('sliders_bg_color', 'text', array(
            'class'     => 'color {required:false}',
            'name'      => 'sliders_bg_color',
            'label'     => Mage::helper('sm_cameraslide')->__('Background Color'),
            'title'     => Mage::helper('sm_cameraslide')->__('Background Color'),
            'value'     => $modelSliders->getData('sliders_bg_color') ? $modelSliders->getData('sliders_bg_color') : 'CCCCCC',
            'require'   => false,
            'onchange'  => 'CmrSl.updateContainer()'
        ));

        $fieldset2->addField('preview_width', 'text', array(
            'class'     => 'validate-number',
            'name'      => 'preview_width',
            'label'     => Mage::helper('sm_cameraslide')->__('Preview Width'),
            'title'     => Mage::helper('sm_cameraslide')->__('Preview Width'),
            'onchange'  => 'CmrSl.collectContainer()',
            'note'      => 'Preview width only use preview in admin. Not use set width of slide in frontend website.',
            'require'   => true,
        ));
        $fieldset2->addField('preview_height', 'text', array(
            'class'     => 'validate-number',
            'name'      => 'preview_height',
            'label'     => Mage::helper('sm_cameraslide')->__('Preview Height'),
            'title'     => Mage::helper('sm_cameraslide')->__('Preview Height'),
            'onchange'  => 'CmrSl.collectContainer()',
            'note'      => 'Preview height only use preview in admin. Not use set height of slide in frontend website.',
            'require'   => true,
        ));

        $fieldset2->addField('layers', 'text', array(
            'label' => Mage::helper('sm_cameraslide')->__('Layers')
        ));

        $form->getElement('layers')->setRenderer(
            $this->getLayout()->createBlock('sm_cameraslide/adminhtml_widget_form_layers')
        );

        $container = $form->addFieldset('container_fieldset', array(
            'class'     => 'no-spacing'
        ));

        $left = $container->addFieldset('left_fieldset', array(
            'class'     => 'no-spacing'
        ));

        $fieldset3 = $left->addFieldset('layer_params_fieldset', array(
            'legend'    => "<i class='fa fa-list-ul'></i>".Mage::helper('sm_cameraslide')->__('Layer General Parameters'),
            'class'     => 'collapsible'
        ));

        $fieldset3->addField('layer_width', 'text', array(
	        'class'     => 'validate-number',
            'label'     => Mage::helper('sm_cameraslide')->__('Width'),
            'title'     => Mage::helper('sm_cameraslide')->__('Width'),
            'note'      => "Width use type \"%\". Ex: 40%, 50%, ... <br/> Note: with layer image, place image can the responsive, you would be best set width to layer",
            'require'   => true,
        ));

        $fieldset3->addField('layer_height', 'text', array(
	        'class'     => 'validate-number',
            'label'     => Mage::helper('sm_cameraslide')->__('Height'),
            'title'     => Mage::helper('sm_cameraslide')->__('Height'),
            'require'   => true,
            'note'      => "Height use type \"%\". Ex: 40%, 50%, ...<br/> Note: with layer image, place image can the responsive, you would be best set width to layer",
        ));

        $fieldset3->addField('layer_min_width', 'text', array(
	        'class'     => 'validate-number',
            'label'     => Mage::helper('sm_cameraslide')->__('Min Width'),
            'title'     => Mage::helper('sm_cameraslide')->__('Min Width'),
            'require'   => true,
            'note'      => "Min width use type \"%\". Ex: 40%, 50%, ...",
        ));

        $fieldset3->addField('layer_min_height', 'text', array(
	        'class'     => 'validate-number',
            'label'     => Mage::helper('sm_cameraslide')->__('Min Height'),
            'title'     => Mage::helper('sm_cameraslide')->__('Min Height'),
            'require'   => true,
            'note'      => "Min height use type \"%\". Ex: 40%, 50%, ...",
        ));

        $fieldset3->addField('layer_bg_color', 'text', array(
            'class'     => 'color {required:false}',
            'label'     => Mage::helper('sm_cameraslide')->__('Layer Background Color'),
            'title'     => Mage::helper('sm_cameraslide')->__('Layer Background Color'),
            'value'     => $modelSliders->getData('layer_bg_color') ? $modelSliders->getData('layer_bg_color') : '222222',
            'note'      => 'Loader with the color you choice',
            'require'   => false,
        ));

        $fieldset3->addField('layer_color', 'text', array(
            'class'     => 'color {required:false}',
            'label'     => Mage::helper('sm_cameraslide')->__('Layer Text Color'),
            'title'     => Mage::helper('sm_cameraslide')->__('Layer Text Color'),
            'value'     => $modelSliders->getData('layer_color') ? $modelSliders->getData('layer_color') : 'EEEEEE',
            'note'      => 'Loader with the color you choice',
            'require'   => false,
        ));

        $fieldset3->addField('layer_text_align', 'select', array(
            'label'     => Mage::helper('sm_cameraslide')->__('Text Align'),
            'title'     => Mage::helper('sm_cameraslide')->__('Text Align'),
            'values'    => $modelSliders->getOptsTextAlign(),
        ));

        $fieldset3->addField('layer_textBold', 'select', array(
            'label'     => Mage::helper('sm_cameraslide')->__('Text Bold'),
            'title'     => Mage::helper('sm_cameraslide')->__('Text Bold'),
            'values'    => $modelSliders->getOptsTextBold(),
        ));

        $fieldset3->addField('layer_textItalic', 'select', array(
            'label'     => Mage::helper('sm_cameraslide')->__('Text Italic'),
            'title'     => Mage::helper('sm_cameraslide')->__('Text Italic'),
            'values'    => $modelSliders->getOptsTextItalic(),
        ));

        $fieldset3->addField('layer_textUnderline', 'select', array(
            'label'     => Mage::helper('sm_cameraslide')->__('Text Underline'),
            'title'     => Mage::helper('sm_cameraslide')->__('Text Underline'),
            'values'    => $modelSliders->getOptsTextUnderline(),
        ));

        $fieldset3->addField('layer_font_family', 'text', array(
            'label'     => Mage::helper('sm_cameraslide')->__('Font Family'),
            'title'     => Mage::helper('sm_cameraslide')->__('Font Family'),
            'note'      => "Ex: \"Open Sans\", Helvetica, Arial, sans-serif <br/>"
                ." Note: Separate each value with a comma. If a font name contains white-space, it must be quoted. Single quotes must be used when using the \"style\" attribute in HTML.",
        ));

        $fieldset3->addField('layer_font_size', 'text', array(
            'label'     => Mage::helper('sm_cameraslide')->__('Font Size'),
            'title'     => Mage::helper('sm_cameraslide')->__('Font Size'),
            'class'     => 'validate-number',
            'require'   => true,
            'note'      => "Only use type number. Ex : 13, 14, ..."
        ));

        $fieldset3->addField('layer_top', 'text', array(
            'label'     => Mage::helper('sm_cameraslide')->__('Top'),
            'title'     => Mage::helper('sm_cameraslide')->__('Top'),
            'class'     => 'validate-number',
            'require'   => true,
            'note'      => "Only use type number. Ex : 50, 100, ..."
        ));

        $fieldset3->addField('layer_left', 'text', array(
            'label'     => Mage::helper('sm_cameraslide')->__('Left'),
            'title'     => Mage::helper('sm_cameraslide')->__('Right'),
            'class'     => 'validate-number',
            'require'   => true,
            'note'      => "Only use type number, Ex : 50, 100, ..."
        ));

        $right = $container->addFieldset('right_fieldset', array(
            'class'     => 'no-spacing'
        ));

        $fieldset4 = $right->addFieldset('layer_time_fieldset', array(
            'legend'    => "<i class='fa fa-sort-numeric-asc'></i>".Mage::helper('sm_cameraslide')->__('Layers Timing & Sorting')
        ));

        $fieldset4->setHeaderBar(
            $this->getLayout()->createBlock('adminhtml/widget_button', '', array(
                'label'     => "<i class='fa fa-list'></i>".Mage::helper('sm_cameraslide')->__('By Depth'),
                'title'     => Mage::helper('sm_cameraslide')->__('By Depth'),
                'type'      => 'button',
                'onclick'   => 'CmrSl.sortLayerItem(this,\'depth\')'
            ))->toHtml()
            .'&nbsp;'.

            $this->getLayout()->createBlock('adminhtml/widget_button', '', array(
                'label'     => "<i class='fa fa-eye'></i>",
                'title'		=> Mage::helper('sm_cameraslide')->__('Hide All Layer'),
                'type'		=> 'button',
                'class'     => 'btn-hide-all normal ',
                'id'		=> 'button_sort_visibility',
                'onclick'   => 'CmrSl.setHideAll()'
            ))->toHtml()
        );

        $fieldset4->addField('list_sorting', 'text', array());
        $form->getElement('list_sorting')->setRenderer(
            $this->getLayout()->createBlock('sm_cameraslide/adminhtml_widget_form_sorting')
        );

        $fieldset5 = $form->addFieldset('layer_animate_fieldset', array(
            'legend'    => "<i class='fa fa-outdent'></i>".Mage::helper('sm_cameraslide')->__('Layer Animation '),
            'class'     => 'collapsible'
        ));

        $fieldset5->addField('layer_animation_preview', 'text', array(
            'label'     => Mage::helper('sm_cameraslide')->__('Preview Transition'),
            'note'      => Mage::helper('sm_cameraslide')->__('Preview Transition (Start & End Time is ignored during demo)')
        ));

        $form->getElement('layer_animation_preview')->setRenderer(
            $this->getLayout()->createBlock('sm_cameraslide/adminhtml_widget_form_animation')
        );

        $fieldset5->addField('layer_class', 'text', array(
            'label'     => Mage::helper('sm_cameraslide')->__('Layer Class'),
            'title'     => Mage::helper('sm_cameraslide')->__('Layer Class'),
            'require'   => false,
            'note'      => 'Class not contain space. Ex: class_1, class_2, ...'
        ));

        $fieldset5->addField('layer_time_transitions', 'text', array(
            'label'     => Mage::helper('sm_cameraslide')->__('Time Transitions Layer'),
            'title'     => Mage::helper('sm_cameraslide')->__('Time Transitions Layer'),
            'require'   => false,
            'onchange'  => 'CmrSl.updateContainer()',
            'note'      => 'Time delay load of a layer in milliseconds'
        ));

        $fieldset5->addField('layer_time_delay_transitions', 'text', array(
            'label'     => Mage::helper('sm_cameraslide')->__('Time Delay Transitions Layer'),
            'title'     => Mage::helper('sm_cameraslide')->__('Time Delay Transitions Layer'),
            'require'   => false,
            'onchange'  => 'CmrSl.updateContainer()',
            'note'      => 'Time delay load of a layer in milliseconds'
        ));

	    $fieldset5->addField('layer_alt_image', 'text', array(
		    'label'     => Mage::helper('sm_cameraslide')->__('Alt Image'),
		    'title'     => Mage::helper('sm_cameraslide')->__('Alt Image'),
		    'onchange'  => 'CmrSl.updateContainer()',
		    'require'   => false,
	    ));

	    $fieldset5->addField('layer_title_image_link', 'text', array(
		    'name'      => 'layer_title_image_link',
		    'label'     => Mage::helper('sm_cameraslide')->__('Title Image Link'),
		    'title'     => Mage::helper('sm_cameraslide')->__('Title Image Link'),
		    'onchange'  => 'CmrSl.updateContainer()',
		    'note'      => "Title image link is name of link and title show when you hover mouse on image"
	    ));

	    $fieldset5->addField('layer_target_link', 'select', array(
		    'name'      => 'layer_target_link',
		    'label'     => Mage::helper('sm_cameraslide')->__('Target Image Link'),
		    'title'     => Mage::helper('sm_cameraslide')->__('Target Image Link'),
		    'values'     => $modelSliders->getDataTarget(),
		    'onchange'  => 'CmrSl.updateContainer()'
	    ));

	    $fieldset5->addField('layer_link', 'text', array(
		    'name'      => 'layer_link',
		    'label'     => Mage::helper('sm_cameraslide')->__('Http Image Link'),
		    'title'     => Mage::helper('sm_cameraslide')->__('Http Image Link'),
		    'onchange'  => 'CmrSl.updateContainer()',
		    'note'      => "Http link can is full link \"http://magento.com, ...\" or short link \"magento.com, ...\". <br/>".
			    "Note: With link type \"https://www.google.com.vn\", \"https://...\", you only can use full link \"https://www.google.com.vn\", \"https://...\""
	    ));

        $fieldset5->addField('layer_text', 'textarea', array(
            'label'     => Mage::helper('sm_cameraslide')->__('Text / Html'),
            'title'     => Mage::helper('sm_cameraslide')->__('Text / Html'),
            'onchange'  => 'CmrSl.updateContainer()'
        ));

        $fieldset5->addField('layer_data_fxin', 'select', array(
            'label'     => Mage::helper('sm_cameraslide')->__('Data Sliders Transitions In'),
            'title'     => Mage::helper('sm_cameraslide')->__('Data Sliders Transitions In'),
            'values'    => $modelSliders->getFadeIn(),
            'onchange'  => 'CmrSl.updateContainer()',
            'note'      => 'Transions in sliders on slide',
            'require'   => false,
        ));

        $fieldset5->addField('layer_data_fxout', 'select', array(
            'label'     => Mage::helper('sm_cameraslide')->__('Data Sliders Transitions Out'),
            'title'     => Mage::helper('sm_cameraslide')->__('Data Sliders Transitions Out'),
            'values'    => $modelSliders->getFadeOut(),
            'onchange'  => 'CmrSl.updateContainer()',
            'note'      => 'Transions out sliders on slide',
            'require'   => false,
        ));

        $fieldset5->addField('layer_data_fadein', 'select', array(
            'label'     => Mage::helper('sm_cameraslide')->__('Data Fade In'),
            'title'     => Mage::helper('sm_cameraslide')->__('Data Fade In'),
            'values'    => $modelSliders->getTrueFalse(),
            'onchange'  => 'CmrSl.updateContainer()',
            'note'      => 'Fade in sliders on slide',
            'require'   => false,
        ));

        $fieldset5->addField('layer_data_fadeout', 'select', array(
            'label'     => Mage::helper('sm_cameraslide')->__('Data Fade Out'),
            'title'     => Mage::helper('sm_cameraslide')->__('Data Fade Out'),
            'values'    => $modelSliders->getTrueFalse(),
            'onchange'  => 'CmrSl.updateContainer()',
            'note'      => 'Fade out sliders on slide',
            'require'   => false,
        ));

        $fieldset5->addField('layer_data_easein', 'select', array(
            'label'     => Mage::helper('sm_cameraslide')->__('Data Easing In'),
            'title'     => Mage::helper('sm_cameraslide')->__('Data Easing In'),
            'values'    => $modelSliders->getDataEasing(),
            'onchange'  => 'CmrSl.updateContainer()',
            'note'      => 'The easing effect for only layer',
            'require'   => false
        ));

        $fieldset5->addField('layer_data_easeout', 'select', array(
            'label'     => Mage::helper('sm_cameraslide')->__('Data Easing Out'),
            'title'     => Mage::helper('sm_cameraslide')->__('Data Easing Out'),
            'values'    => $modelSliders->getDataEasing(),
            'onchange'  => 'CmrSl.updateContainer()',
            'note'      => 'The easing effect for only layer',
            'require'   => false
        ));

        Mage::dispatchEvent('adminhtml_sliders_edit_tab_main_prepare_form', array('form' => $form));
        /*
         * Điều kiện if sẽ phân biệt trường hợp add và edit là khác nhau
         *
         * + nếu có Id thì sẽ setValues với values của id đó.
         * + nếu ko có Id thì sẽ setValues với values mặc định của addField
         * */
        if($modelSliders->getId()){
            $form->setValues($modelSliders->getData());
        }
        $this->setForm($form);

        $this->setChild('form_after', $this->getLayout()->createBlock('adminhtml/widget_form_element_dependence')
            ->addFieldMap($bg->getHtmlId(), $bg->getName())
            ->addFieldMap($bg1->getHtmlId(), $bg1->getName())
            ->addFieldMap($bg6->getHtmlId(), $bg6->getName())
            ->addFieldDependence($bg1->getName(), $bg->getName(), 'image')
            ->addFieldDependence($bg6->getName(), $bg->getName(), 'color')
        );
        return parent::_prepareForm();
    }
}