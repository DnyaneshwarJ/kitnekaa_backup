<?php
/**
 * Created by PhpStorm.
 * User: Vu Van Phan
 * Date: 03-02-2015
 * Time: 15:03
 */
class Sm_Cameraslide_Block_Adminhtml_Widget_Form_Browsers extends Mage_Adminhtml_Block_Widget implements Varien_Data_Form_Element_Renderer_Interface{
    protected $_element;

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('sm/cameraslide/widget/form/browser.phtml');
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
        $this->setElement($this->getData( 'element' ) );
        $browsersBtn = $this->getLayout()->createBlock('adminhtml/widget_button', 'button', array(
            'label'     => "<i class='fa fa-cloud-upload'></i>",
            'title'     => Mage::helper('sm_cameraslide')->__('Click to browsers'),
            'type'      => 'button',
            'onclick'   => sprintf( '_MediabrowserUtility.openDialog(\'%s\')', Mage::getSingleton( 'adminhtml/url' )->getUrl( 'adminhtml/cms_wysiwyg_images/index', array(
                'static_urls_allowed' => 1,
                'target_element_id' => $this->getElement()->getHtmlId()
            ) ) )
        ));
        $this->setChild( 'browsersBtn', $browsersBtn );
        $clearBtn = $this->getLayout()->createBlock( 'adminhtml/widget_button', 'button', array(
            'label' => "<i class='fa fa-times-circle'></i>",
            'title' => Mage::helper( 'sm_cameraslide' )->__( 'Click to clear' ),
            'type' => 'button',
            'onclick' => "on_{$this->getElement()->getHtmlId()}_clear_click();"
        ) );
        $this->setChild( 'clearBtn', $clearBtn );
        return parent::_prepareLayout();
    }
}