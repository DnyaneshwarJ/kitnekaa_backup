<?php
/**
 * Created by PhpStorm.
 * User: Vu Van Phan
 * Date: 31-01-2015
 * Time: 7:49
 */
class Sm_Cameraslide_Block_Adminhtml_Widget_Form_Animation extends Mage_Adminhtml_Block_Widget implements Varien_Data_Form_Element_Renderer_Interface
{
    protected $_element;

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('sm/cameraslide/widget/form/animation.phtml');
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
        parent::_prepareLayout();
    }
}