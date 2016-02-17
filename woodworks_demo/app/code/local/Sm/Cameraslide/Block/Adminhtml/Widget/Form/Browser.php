<?php
/**
 * Created by PhpStorm.
 * User: Vu Van Phan
 * Date: 26-02-2015
 * Time: 14:38
 */
class Sm_Cameraslide_Block_Adminhtml_Widget_Form_Browser extends Sm_Cameraslide_Block_Adminhtml_Widget_Form_Browsers
{
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $browsersBtn = $this->getLayout()->createBlock( 'adminhtml/widget_button', 'button', array(
            'label' => "<i class='fa fa-film'></i>",
            'title' => Mage::helper( 'sm_cameraslide' )->__( 'Click to browser media' ),
            'type' => 'button',
            'onclick' => sprintf( '_MediabrowserUtility.openDialog(\'%s\', \'browserVideoWindow\', null, null, \'%s\')', Mage::getSingleton( 'adminhtml/url' )->getUrl( 'adminhtml/cms_wysiwyg_images/index', array(
                'static_urls_allowed' => 1,
                'target_element_id' => $this->getElement()->getHtmlId(),
                'type' => 'media',
                'onInsertCallback' => 'CmrSl.onSelectHtml5Video',
                'onInsertCallbackParams' => 'browserVideoWindow'
            ) ), Mage::helper( 'sm_cameraslide' )->__( 'Select Video' ) )
        ) );
        $this->setChild( 'browsersBtn', $browsersBtn );
        return $this;
    }
}