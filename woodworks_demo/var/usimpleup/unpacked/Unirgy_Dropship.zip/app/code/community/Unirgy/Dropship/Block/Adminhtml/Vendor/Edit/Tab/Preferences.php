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

class Unirgy_Dropship_Block_Adminhtml_Vendor_Edit_Tab_Preferences extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();
        $this->setDestElementId('vendor_preferences');
        //$this->setTemplate('udropship/vendor/form.phtml');
    }

    protected function _prepareForm()
    {
        $vendor = Mage::registry('vendor_data');
        $hlp = Mage::helper('udropship');
        $id = $this->getRequest()->getParam('id');
        $form = new Varien_Data_Form();
        $this->setForm($form);

        if (!$vendor) {
            $vendor = Mage::getModel('udropship/vendor');
        }
        $vendorData = $vendor->getData();

        $source = Mage::getSingleton('udropship/source');

        $fieldsets = array();
        foreach (Mage::getConfig()->getNode('global/udropship/vendor/fieldsets')->children() as $code=>$node) {
            if ($node->modules && !$hlp->isModulesActive((string)$node->modules)
                || $node->hide_modules && $hlp->isModulesActive((string)$node->hide_modules)
                || $node->is('hidden')
            ) {
                continue;
            }
            $fieldsets[$code] = array(
                'position' => (int)$node->position,
                'params' => array(
                    'legend' => Mage::helper('udropship')->__((string)$node->legend),
                ),
            );
        }
        foreach (Mage::getConfig()->getNode('global/udropship/vendor/fields')->children() as $code=>$node) {
            if (empty($fieldsets[(string)$node->fieldset]) || $node->is('disabled')) {
                continue;
            }
            if ($node->modules && !$hlp->isModulesActive((string)$node->modules)
                || $node->hide_modules && $hlp->isModulesActive((string)$node->hide_modules)
            ) {
                continue;
            }
            $type = $node->type ? (string)$node->type : 'text';
            $field = array(
                'position' => (float)$node->position,
                'type' => $type,
                'params' => array(
                    'name' => $node->name ? (string)$node->name : $code,
                    'class' => (string)$node->class,
                    'label' => Mage::helper('udropship')->__((string)$node->label),
                    'note' => Mage::helper('udropship')->__((string)$node->note),
                    'field_config' => $node
                ),
            );
            if ($node->name && (string)$node->name != $code && !isset($vendorData[$code])) {
                $vendorData[$code] = @$vendorData[(string)$node->name];
            }
            if ($node->frontend_model) {
                $field['type'] = $code;
                $this->addAdditionalElementType($code, $node->frontend_model);
            }
            switch ($type) {
            case 'statement_po_type': case 'payout_po_status_type': case 'notify_lowstock':
            case 'select': case 'multiselect': case 'checkboxes': case 'radios':
                $source = Mage::getSingleton($node->source_model ? (string)$node->source_model : 'udropship/source');
                if (is_callable(array($source, 'setPath'))) {
                    $source->setPath($node->source ? (string)$node->source : $code);
                }
                if (in_array($type, array('multiselect', 'checkboxes', 'radios')) || !is_callable(array($source, 'toOptionHash'))) {
                    $field['params']['values'] = $source->toOptionArray();
                } else {
                    $field['params']['options'] = $source->toOptionHash();
                }
                break;

            case 'date': case 'datetime':
                $field['params']['image'] = $this->getSkinUrl('images/grid-cal.gif');
                $field['params']['input_format'] = Varien_Date::DATE_INTERNAL_FORMAT;
                $field['params']['format'] = Varien_Date::DATE_INTERNAL_FORMAT;#Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
                break;
            }
            $fieldsets[(string)$node->fieldset]['fields'][$code] = $field;
        }

        uasort($fieldsets, array($hlp, 'usortByPosition'));
        foreach ($fieldsets as $k=>$v) {
            if (empty($v['fields'])) {
                continue;
            }
            $fieldset = $form->addFieldset($k, $v['params']);
            $this->_addElementTypes($fieldset);
            uasort($v['fields'], array($hlp, 'usortByPosition'));
            foreach ($v['fields'] as $k1=>$v1) {
                $fieldset->addField($k1, $v1['type'], $v1['params']);
            }
        }

        $form->setValues($vendorData);

        return parent::_prepareForm();
    }

    protected $_additionalElementTypes = null;
    protected function _initAdditionalElementTypes()
    {
        if (is_null($this->_additionalElementTypes)) {
        $this->_additionalElementTypes = array(
            'wysiwyg' => Mage::getConfig()->getBlockClassName('udropship/adminhtml_vendor_helper_form_wysiwyg'),
            'statement_po_type' => Mage::getConfig()->getBlockClassName('udropship/adminhtml_vendor_helper_form_statementPoType'),
            'payout_po_status_type' => Mage::getConfig()->getBlockClassName('udropship/adminhtml_vendor_helper_form_PayoutPoStatusType'),
            'notify_lowstock' => Mage::getConfig()->getBlockClassName('udropship/adminhtml_vendor_helper_form_notifyLowstock'),
        );
        }
        return $this;
    }
    protected function _getAdditionalElementTypes()
    {
        $this->_initAdditionalElementTypes();
        return $this->_additionalElementTypes;
    }
    public function addAdditionalElementType($code, $class)
    {
        $this->_initAdditionalElementTypes();
        $this->_additionalElementTypes[$code] = Mage::getConfig()->getBlockClassName($class);
        return $this;
    }
}