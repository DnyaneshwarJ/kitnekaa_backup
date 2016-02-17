<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    Unirgy_Dropship
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

class Unirgy_Dropship_Block_Adminhtml_Vendor_Statement_New extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
        $this->_objectId = 'id';
        $this->_blockGroup = 'udropship';
        $this->_mode = 'new';
        $this->_controller = 'adminhtml_vendor_statement';

        $this->_updateButton('save', 'label', Mage::helper('udropship')->__('Generate Statements'));
        $this->_removeButton('delete');
    }

    public function getHeaderText()
    {
        return Mage::helper('udropship')->__('Generate Vendor Statements');
    }

}
