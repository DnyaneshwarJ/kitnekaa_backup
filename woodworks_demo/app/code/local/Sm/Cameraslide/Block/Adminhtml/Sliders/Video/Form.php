<?php
/**
 * Created by PhpStorm.
 * User: Vu Van Phan
 * Date: 15-05-2015
 * Time: 16:56
 */
class Sm_Cameraslide_Block_Adminhtml_Sliders_Video_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $modelSliders = Mage::getModel('sm_cameraslide/sliders');

        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'method' => 'post'
        ));

        $view = $form->addFieldset('video_view_fieldset', array(
            'legend' => $this->helper('sm_cameraslide')->__('Video Preview')
        ));

        $view->addField('video_title', 'text', array(
            'label' => $this->helper('sm_cameraslide')->__('Video Title')
        ));

        $view->addField('video_thumb', 'text', array(
            'label' => $this->helper('sm_cameraslide')->__('Video Thumb')
        ));

        $form->getElement('video_thumb')->setRenderer($this->getLayout()->createBlock('sm_cameraslide/adminhtml_widget_form_thumb'));

        $fieldset = $form->addFieldset('video_info_fieldset', array(
            'legend' => $this->helper('sm_cameraslide' )->__('Video Settings')
        ));
	    if ( $serial = $this->getRequest()->getParam( 'serial' ) )
	    {
		    $fieldset->addField( 'video_serial', 'hidden', array(
			    'value' => $serial
		    ) );
	    }

        $bg = $fieldset->addField('service_video', 'select', array(
            'name'      => 'service_video',
            'label'     => Mage::helper('sm_cameraslide')->__('Select Service Video'),
            'title'     => Mage::helper('sm_cameraslide')->__('Select Service Video'),
            'values'    => $modelSliders->getServiceVideo(),
            'required'  => true,
	        'onchange' => 'CmrSl.onChangeVideoType(this)'
        ));

        $bg1a = $fieldset->addField('src_video', 'text', array(
            'name'      => 'src_video',
            'label'     => Mage::helper('sm_cameraslide')->__('Enter video ID or URL'),
            'title'     => Mage::helper('sm_cameraslide')->__('Enter video ID or URL'),
            'required'  => true,
            'note'      => 'Ex: 94y6svVU4so or 30300114',
        ));

        $s = $fieldset->addField('video_search', 'text', array ());
        $form->getElement('video_search')->setRenderer( $this->getLayout()->createBlock( 'sm_cameraslide/adminhtml_widget_form_search', '', array(
            'element' => $s
        ) ) );

	    $poster = $fieldset->addField( 'video_poster', 'text', array(
		    'name' => 'video_poster',
		    'label' => $this->helper( 'sm_cameraslide' )->__( 'Poster Image Url' ),
		    'title' => $this->helper( 'sm_cameraslide' )->__( 'Poster Image Url' ),
		    'note' => $this->helper( 'sm_cameraslide' )->__( 'Ex: http://video-js.zencoder.com/oceans-clip.png' )
	    ) );

	    $form->getElement('video_poster')->setRenderer($this->getLayout()->createBlock('sm_cameraslide/adminhtml_widget_form_browsers', '', array(
		    'element'   => $poster
	    )));

        $bg31 = $fieldset->addField('video_html5_mp4', 'text', array(
            'name'      => 'html5_mp4_video',
            'label'     => Mage::helper('sm_cameraslide')->__('Video MP4 Url'),
            'title'     => Mage::helper('sm_cameraslide')->__('Video MP4 Url'),
            'note'      => 'Video mp4 choice to PC you or source other',
        ));

        $form->getElement('video_html5_mp4')->setRenderer($this->getLayout()->createBlock('sm_cameraslide/adminhtml_widget_form_browser', '', array(
            'element'   => $bg31
        )));

        $bg32 = $fieldset->addField('video_html5_webm', 'text', array(
            'name'      => 'html5_webm_video',
            'label'     => Mage::helper('sm_cameraslide')->__('Video WEBM Url'),
            'title'     => Mage::helper('sm_cameraslide')->__('Video WEBM Url'),
            'note'      => 'Video webm choice to PC you or source other',
        ));

        $form->getElement('video_html5_webm')->setRenderer($this->getLayout()->createBlock('sm_cameraslide/adminhtml_widget_form_browser', '', array(
            'element'   => $bg32
        )));

        $bg33 = $fieldset->addField('video_html5_ogg', 'text', array(
            'name'      => 'html5_ogg_video',
            'label'     => Mage::helper('sm_cameraslide')->__('Video OGG Url'),
            'title'     => Mage::helper('sm_cameraslide')->__('Video OGG Url'),
            'note'      => 'Video ogg choice to PC you or source other',
        ));

        $form->getElement('video_html5_ogg')->setRenderer($this->getLayout()->createBlock('sm_cameraslide/adminhtml_widget_form_browser', '', array(
            'element'   => $bg33
        )));

        $fieldset->addField('video_width', 'text', array(
	        'class' => 'validate-number',
	        'required' => true,
            'name'      => 'video_width',
            'label'     => Mage::helper('sm_cameraslide')->__('Video Width'),
            'title'     => Mage::helper('sm_cameraslide')->__('Video Width'),
            'value'     => '50',
            'note'      => "Use type \"%\". Ex: 40%, 50%, ...",
        ));

        $fieldset->addField('video_height', 'text', array(
	        'class' => 'validate-number',
	        'required' => true,
            'name'      => 'video_height',
            'label'     => Mage::helper('sm_cameraslide')->__('Video Height'),
            'title'     => Mage::helper('sm_cameraslide')->__('Video Height'),
            'value'    => '50',
            'note'      => "Use type \"%\". Ex: 40%, 50%, ...",
        ));

        $fieldset->addField('video_fullwidth', 'checkbox', array(
            'name' => 'video_fullwidth',
            'label' => $this->helper( 'sm_cameraslide' )->__( 'Full Width' ),
            'title' => $this->helper( 'sm_cameraslide' )->__( 'Full Width' ),
            'onchange' => 'CmrSl.onChangeVideoFullWidth(this)'
        ));

        $fieldset->addField('video_loop', 'checkbox', array(
            'name'      => 'video_loop',
            'label'     => Mage::helper('sm_cameraslide')->__('Video Loop'),
            'title'     => Mage::helper('sm_cameraslide')->__('Video Loop'),
        ));

        $controls = $fieldset->addField('video_controls', 'checkbox', array(
            'name'      => 'video_controls',
            'label'     => Mage::helper('sm_cameraslide')->__('Show Controls'),
            'title'     => Mage::helper('sm_cameraslide')->__('Show Controls'),
        ));

        $fieldset->addField('video_autoplay', 'checkbox', array(
            'name'      => 'video_autoplay',
            'label'     => Mage::helper('sm_cameraslide')->__('Auto Play'),
            'title'     => Mage::helper('sm_cameraslide')->__('Auto Play'),
            'note'      => 'Video auto play but time load of slide is running. You want the watch full video,you can click on the video slide or pause slide to watch full video'
        ));

        $muted = $fieldset->addField('video_muted', 'checkbox', array(
            'name'      => 'video_muted',
            'label'     => Mage::helper('sm_cameraslide')->__('Mute'),
            'title'     => Mage::helper('sm_cameraslide')->__('Mute'),
        ));

	    $fieldset->addField( 'video_args', 'text', array(
		    'name' => 'video_args',
		    'label' => $this->helper('sm_cameraslide')->__('Video Paramaters'),
		    'title' => $this->helper('sm_cameraslide')->__('Video Paramaters')
	    ) );

        $this->setChild('form_after', $this->getLayout()->createBlock('adminhtml/widget_form_element_dependence')
            ->addFieldMap($bg->getHtmlId(), $bg->getName())
            ->addFieldMap($bg1a->getHtmlId(), $bg1a->getName())
            ->addFieldMap($s->getHtmlId(), $s->getName())
            ->addFieldMap($bg31->getHtmlId(), $bg31->getName())
            ->addFieldMap($bg32->getHtmlId(), $bg32->getName())
            ->addFieldMap($bg33->getHtmlId(), $bg33->getName())
            ->addFieldMap($controls->getHtmlId(), $controls->getName())
            ->addFieldMap($muted->getHtmlId(), $muted->getName())
            ->addFieldMap($poster->getHtmlId(), $poster->getName())
            ->addFieldDependence($bg1a->getName(), $bg->getName(), array('youtube', 'vimeo'))
            ->addFieldDependence($s->getName(), $bg->getName(), array('youtube', 'vimeo'))
            ->addFieldDependence($controls->getName(), $bg->getName(), array('youtube', 'html5'))
            ->addFieldDependence($bg31->getName(), $bg->getName(), 'html5')
            ->addFieldDependence($bg32->getName(), $bg->getName(), 'html5')
            ->addFieldDependence($bg33->getName(), $bg->getName(), 'html5')
            ->addFieldDependence($muted->getName(), $bg->getName(), 'html5')
            ->addFieldDependence($poster->getName(), $bg->getName(), 'html5')
        );

        $form->setUseContainer( true );
        $this->setForm( $form );
        return parent::_prepareForm();
    }

    public function getVideoServices()
    {
        return array(
            array(
                'value' => 'youtube',
                'label' => $this->helper( 'sm_cameraslide' )->__( 'Youtube' )
            ),
            array(
                'value' => 'vimeo',
                'label' => $this->helper( 'sm_cameraslide' )->__( 'Vimeo' )
            ),
            array(
                'value' => 'html5',
                'label' => $this->helper( 'sm_cameraslide' )->__( 'HTML5' )
            )
        );
    }
}