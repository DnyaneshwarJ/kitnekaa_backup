<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Attribute
 *
 * @author root
 */
class Amar_Profile_Block_Adminhtml_Customer_Attribute extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct() {
        $this->_blockGroup = "profile";
        $this->_controller = "adminhtml_customer_attribute";
        $this->_addButtonLabel = $this->__('Add New Attribute');
        $this->_headerText = $this->__("Manage Customer Attributes");
        parent::__construct();
    }
}

?>
