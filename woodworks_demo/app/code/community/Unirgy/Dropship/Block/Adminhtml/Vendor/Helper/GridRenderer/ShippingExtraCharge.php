<?php

class Unirgy_Dropship_Block_Adminhtml_Vendor_Helper_GridRenderer_ShippingExtraCharge extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $html = '';
        $hlp = Mage::helper('udropship');
        $v = $this->getColumn()->getVendor();
        $fieldIdTpl = $this->getColumn()->getData('field_id_tpl');
        $fields = array(
            'extra_charge_suffix' => array('label'=>'Suffix'),
            'extra_charge_type' => array('label'=>'Type', 'select'=>1, 'options_path'=>'shipping_extra_charge_type'),
            'extra_charge' => array('label'=>'Value'),
        );
        $useDefaultLbl = Mage::helper('udropship')->__('Use Default');
        $htmlId = '_'.md5(uniqid(microtime(), true));
        $fieldsHtml = array();
        foreach ($fields as $field=>$fieldData) {
            $fieldsHtml[$field] = $fields[$field];
            $fieldsHtml[$field]['id'] = sprintf($fieldIdTpl, $field);
            $fieldsHtml[$field]['use_default_id'] = sprintf($fieldIdTpl, $field.'_use_default');
            $fieldsHtml[$field]['use_default'] = false;
            $fieldsHtml[$field]['use_default_checked_html'] = '';
            $fieldsHtml[$field]['disabled_html'] = '';
            $fieldsHtml[$field]['value'] = $row->getData($field);
            if (null === $fieldsHtml[$field]['value']) {
                $fieldsHtml[$field]['use_default'] = true;
                $fieldsHtml[$field]['use_default_checked_html'] = 'checked="checked"';
                $fieldsHtml[$field]['disabled_html'] = 'disabled="disabled"';
                $fieldsHtml[$field]['value'] = $v->getData('default_shipping_'.$field);
            }
            if (is_numeric($fieldsHtml[$field]['value'])) {
                $fieldsHtml[$field]['value'] *= 1;
            }
            $fieldsHtml[$field]['html_label'] = htmlspecialchars($fieldsHtml[$field]['label']);
            $fieldsHtml[$field]['html_value'] = htmlspecialchars($fieldsHtml[$field]['value']);
        }
        foreach ($fieldsHtml as &$fieldHtml) {
            $fieldHtml['html'] = '<nobr><br />';
            if (!empty($fieldHtml['select'])) {
                $options = array();
                if (!empty($fieldHtml['options_path'])) {
                    $options = Mage::getSingleton('udropship/source')->setPath($fieldHtml['options_path'])->toOptionHash();
                }
                $fieldHtml['html'] .= "{$fieldHtml['html_label']}  <select id='{$htmlId}{$fieldHtml['id']}' name='{$fieldHtml['id']}' {$fieldHtml['disabled_html']}>";
                foreach ($options as $value => $label) {
                    $_selHtml = $value == $fieldHtml['value'] ? 'selected="selected"' : '';
                    $fieldHtml['html'] .= "<option value='{$value}' $_selHtml>$label</option>";
                }
                $fieldHtml['html'] .= "</select>";
            } else {
                $fieldHtml['html'] .= "{$fieldHtml['html_label']} <input id='{$htmlId}{$fieldHtml['id']}' type='text' name='{$fieldHtml['id']}' value='{$fieldHtml['html_value']}' {$fieldHtml['disabled_html']} />";
            }
            $fieldHtml['html'] .= "<input id='{$htmlId}{$fieldHtml['use_default_id']}' type='checkbox' name='{$fieldHtml['use_default_id']}' value='1' {$fieldHtml['use_default_checked_html']} /> $useDefaultLbl";
            $fieldHtml['html'] .= '</nobr>';
        }
        unset($fieldHtml);
        $aexId = $this->getColumn()->getId();
        $aexVal = $row->getData('allow_extra_charge');
        $aexCheckedHtml = $aexVal ? 'checked="checked"' : '';
        $aexLbl = Mage::helper('udropship')->__('Allow Extra Charge');
        $_fieldsHtml = '';
        foreach ($fieldsHtml as $fieldHtml) {
            $_fieldsHtml .= $fieldHtml['html'];
        }
        $_fieldsDisplay = $aexVal ? 'block' : 'none';
        $html .=<<<EOT
<nobr>
<input id="{$htmlId}_allow" onclick="" type="checkbox" name="{$aexId}" value="1" $aexCheckedHtml /> $aexLbl
</nobr>
<div id="{$htmlId}_fields" style="display: {$_fieldsDisplay}">
$_fieldsHtml
</div>
<script type="text/javascript">
    $('{$htmlId}_allow').observe('click', function(event){
        if ($('{$htmlId}_allow').checked) {
            $('{$htmlId}_fields').show();
        } else {
            $('{$htmlId}_fields').hide();
        }
        even.stop();
    })
</script>
EOT;
        return $html;
    }
}