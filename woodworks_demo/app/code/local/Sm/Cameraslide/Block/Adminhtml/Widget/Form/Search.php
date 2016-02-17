<?php
/**
 * Created by PhpStorm.
 * User: Vu Van Phan
 * Date: 16-05-2015
 * Time: 17:33
 */
class Sm_Cameraslide_Block_Adminhtml_Widget_Form_Search extends Mage_Adminhtml_Block_Widget implements Varien_Data_Form_Element_Renderer_Interface
{
    protected $_element;

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate( 'sm/cameraslide/widget/form/search.phtml' );
    }

    public function getElement()
    {
        return $this->_element;
    }

    public function setElement( Varien_Data_Form_Element_Abstract $element )
    {
        return $this->_element = $element;
    }

    public function render( Varien_Data_Form_Element_Abstract $element )
    {
        $this->setElement( $element );
        return $this->toHtml();
    }

    protected function _prepareLayout()
    {
        $this->setElement( $this->getData( 'element' ) );
        $this->setChild( 'btn', $this->getLayout()->createBlock( 'adminhtml/widget_button', '', array(
            'label' => Mage::helper('sm_cameraslide')->__( 'Search' ),
            'type' => 'button',
            'id' => $this->getElement()->getHtmlId(),
            'onclick' => 'CmrSl.searchVideo()'
        ) ) );
        return parent::_prepareLayout();
    }
}