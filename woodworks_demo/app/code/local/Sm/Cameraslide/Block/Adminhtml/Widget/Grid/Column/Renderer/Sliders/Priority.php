<?php
/**
 * Created by PhpStorm.
 * User: Vu Van Phan
 * Date: 04-02-2015
 * Time: 9:03
 */
class Sm_Cameraslide_Block_Adminhtml_Widget_Grid_Column_Renderer_Sliders_Priority extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    protected $_variablePattern = '/\\$([a-z0-9_]+)/i';
    public function _getValue(Varien_Object $row)
    {
        $format         = ($this->getColumn()->getFormat()) ? $this->getColumn()->getFormat() : null;
        $defaultValue   = $this->getColumn()->getDefault();
        $htmlId         = 'editable_'.$row->getId();
        $saveUrl        = $this->getUrl('*/*/ajaxSave');
        if(is_null($format))
        {
            $data   = $row['priority'];
            $string = is_null($data) ? $defaultValue : $data;
            $html   = sprintf('<div id="%s" control="text" saveUrl="%s" attr="%s" entity="%s" class="editable">%s</div>', $htmlId, $saveUrl, 'priority', $row->getId(), $this->escapeHtml($string));
        }
        elseif(preg_match_all($this->_variablePattern, $format, $matches))
        {
            $formattedString = $format;
            foreach ($matches[0] as $matcheIndex=>$match) {
                $value              = $row->getData($matches[1][$matcheIndex]);
                $formattedString    = str_replace($match, $value, $formattedString);
            }
            $html = sprintf('<div id="%s" control="text" saveUrl="%s" attr="%s" entity="%s" class="editable">%s</div>', $htmlId, $saveUrl, $this->getColumn()->getIndex(), $row->getId(), $formattedString);
        }else{
            $html = sprintf('<div id="%s" control="text" saveUrl="%s" attr="%s" entity="%s" class="editable">%s</div>', $htmlId, $saveUrl, $this->getColumn()->getIndex(), $row->getId(), $this->escapeHtml( $format ));
        }
        return $html."<script>if (bindInlineEdit) bindInlineEdit('{$htmlId}');</script>";
    }
}