<?php
/**
 * Quote View Form class
 * @category    Bobcares
 * @package     Bobcares_Quote2sales
 */

class Bobcares_Quote2Sales_Block_Adminhtml_Quote_View_Form extends Mage_Adminhtml_Block_Template
{

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('bobcares/quote2sales/quote/view/form.phtml');
    }
}