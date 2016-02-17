<?php

class Unirgy_Dropship_Block_Vendor_Htmlselect extends Varien_Data_Form_Element_Select
{
    public function getValues()
    {
        $values = $this->getData('values');
        return empty($values) ? Mage::getSingleton('udropship/source')->setPath('vendors')->toOptionArray() : $values;
    }
    protected function _getValues()
    {
        return Mage::getSingleton('udropship/source')->setPath('vendors')->toOptionHash();
    }
    public function getNameValue()
    {
        $values = $this->_getValues();
        $value = $this->_getData('value');
        return isset($values[$value]) ? $values[$value] : $value;
    }
    public function getEscapedNameValue()
    {
        return $this->_escape($this->getNameValue());
    }
    public function getElementHtml()
    {
        if (Mage::getStoreConfigFlag('udropship/vendor/autocomplete_htmlselect')) {
            $html = '<input id="_autocomplete_'.$this->getHtmlId().'" class="input-text" name="_autocomplete_'.$this->getName()
             .'" value="'.$this->getEscapedNameValue().'" '.$this->serialize($this->getHtmlAttributes()).'/>'."\n";
            $html .= '
            <input type="hidden" name="'.$this->getName().'" id="'.$this->getHtmlId().'" value="'.$this->getEscapedValue().'">
            <div class="autocomplete" style="font-weight:bold; display: none;" id="_autocomplete_container_'.$this->getHtmlId().'"></div>
            <script type="text/javascript">
            	(function () {
                	var acObserve = function(){
                    	if ($("_autocomplete_'.$this->getHtmlId().'").value=="") $("'.$this->getHtmlId().'").value = ""
                	}
                    $("_autocomplete_'.$this->getHtmlId().'").observe("change", acObserve)
                    $("_autocomplete_'.$this->getHtmlId().'").observe("click", acObserve)
                	new Ajax.Autocompleter(
                        "_autocomplete_'.$this->getHtmlId().'",
                        "_autocomplete_container_'.$this->getHtmlId().'",
                        "'.Mage::getModel('core/url')->getUrl('udropship/index/vendorAutocomplete').'",
                        {
                            paramName: "vendor_name",
                            method: "get",
                            minChars: 2,
                            updateElement: function(el) {
                                $("'.$this->getHtmlId().'").value = el.title;
                                $("_autocomplete_'.$this->getHtmlId().'").value = el.innerHTML.stripTags();
                			},
                            onShow : function(element, update) {
                                if(!update.style.position || update.style.position=="absolute") {
                                    update.style.position = "absolute";
                                    Position.clone(element, update, {
                                        setHeight: false,
                                        offsetTop: element.offsetHeight
                                    });
                                }
                                Effect.Appear(update,{duration:0});
                            }

        	            }
        	        )
    	        })()
            </script>
            ';
            $html.= $this->getAfterElementHtml();
        } else {
            $html = parent::getElementHtml();
        }
        return $html;
    }

    public function getHtmlAttributes()
    {
        if (Mage::getStoreConfigFlag('udropship/vendor/autocomplete_htmlselect')) {
            return array('type', 'title', 'class', 'style', 'onclick', 'onchange', 'onkeyup', 'disabled', 'readonly', 'maxlength', 'tabindex');
        } else {
            return parent::getHtmlAttributes();
        }
    }
}
