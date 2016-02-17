<?php

class Unirgy_Dropship_Block_CategoriesMultiSelect extends Varien_Data_Form_Element_Multiselect
{
    protected function _optionToHtml($option, $selected)
    {
        if (is_array($option['value'])) {
            $html ='<optgroup label="'.$option['label'].'">'."\n";
            foreach ($option['value'] as $groupItem) {
                $html .= $this->_optionToHtml($groupItem, $selected);
            }
            $html .='</optgroup>'."\n";
        }
        else {
            $level = isset($option['level']) ? $option['level'] : 0;
            $style = isset($option['style']) ? $option['style'] : '';
            //$style .= ' padding-left: '.round($level*16).'px';
            $html = "<option value=\"{$this->_escape($option['value'])}\" class='level-{$level}' style=\"{$style}\"";
            $html.= isset($option['title']) ? ' title="'.$this->_escape($option['title']).'"' : '';
            if (!$this->getSkipDisabled()) {
                $html.= isset($option['disabled']) ? ' disabled="disabled" readonly="readonly"' : '';
            }
            if (in_array($option['value'], $selected)) {
                $html.= ' selected="selected"';
            }
            $label = $this->_escape($option['label']);
            $label = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level).$label;
            $html.= '>'.$label. '</option>'."\n";
        }
        return $html;
    }
}