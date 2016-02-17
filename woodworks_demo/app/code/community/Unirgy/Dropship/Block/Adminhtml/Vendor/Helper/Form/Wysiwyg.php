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

class Unirgy_Dropship_Block_Adminhtml_Vendor_Helper_Form_Wysiwyg extends Varien_Data_Form_Element_Textarea
{
    public function getAfterElementHtml()
    {
        $html = parent::getAfterElementHtml();
        if ($this->isWysiwygAllowed()) {
            $html .= Mage::getSingleton('core/layout')
                ->createBlock('adminhtml/widget_button', '', array(
                    'label'   => Mage::helper('udropship')->__('WYSIWYG Editor'),
                    'type'    => 'button',
                    'disabled' => false,
                    'class' => '',
                    'onclick' => 'uVendorWysiwygEditor.open(\''.Mage::helper('adminhtml')->getUrl('*/*/wysiwyg').'\', \''.$this->getHtmlId().'\')'
                ))->toHtml();
        }
        return $html;
    }

    public function isWysiwygAllowed()
    {
        return Mage::helper('udropship')->isWysiwygAllowed();
    }
}

