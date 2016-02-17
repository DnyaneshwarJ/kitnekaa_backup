<?php
/**
 * Created by PhpStorm.
 * User: Vu Van Phan
 * Date: 16-05-2015
 * Time: 17:03
 */
class Sm_Cameraslide_Block_Adminhtml_Widget_Form_Thumb extends Mage_Adminhtml_Block_Widget implements Varien_Data_Form_Element_Renderer_Interface
{
    protected $_element;

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('sm/cameraslide/widget/form/thumb.phtml');
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
}