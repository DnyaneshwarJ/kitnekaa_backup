<?php

class Unirgy_Dropship_Block_Vendor_GridColumnFilter extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Select
{
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
        return $this->htmlEscape($this->getNameValue());
    }
    static protected $_gridInit = false;
    public function getHtml()
    {
        $gridId = $gridHtmlObjName = '';
        if ($this->getColumn() && $this->getColumn()->getGrid()) {
            $gridHtmlObjName = $this->getColumn()->getGrid()->getJsObjectName();
            $gridId = $this->getColumn()->getGrid()->getId();
        }
        if (Mage::getStoreConfigFlag('udropship/vendor/autocomplete_htmlselect')) {
            $html = '<input id="_autocomplete_'.$this->_getHtmlId().'" class="input-text" name="_autocomplete_'.$this->_getHtmlName()
             .'" value="'.$this->getEscapedNameValue().'" '.$this->serialize($this->getHtmlAttributes()).'/>'."\n";
            $html .= '
            <input type="hidden" name="'.$this->_getHtmlName().'" id="'.$this->_getHtmlId().'" value="'.$this->getEscapedValue().'">
            <div class="autocomplete" style="display: none; font-weight:bold;" id="_autocomplete_container_'.$this->_getHtmlId().'"></div>
            <script type="text/javascript">
            	var acObserve = function(){
                	if ($("_autocomplete_'.$this->_getHtmlId().'").value=="") $("'.$this->_getHtmlId().'").value = ""
            	}
            	$("_autocomplete_'.$this->_getHtmlId().'").observe("change", acObserve)
            	varienGlobalEvents.attachEventHandler("uGridInitAfter", function(gridObj) {
            		if (gridObj.containerId=="'.$gridId.'") {
        				gridObj.filterKeyPress = gridObj.filterKeyPress.wrap(function(proceed, event){
        					acObserve()
        					proceed(event)
        				})
        			}
        		})
            	new Ajax.Autocompleter(
                    "_autocomplete_'.$this->_getHtmlId().'",
                    "_autocomplete_container_'.$this->_getHtmlId().'",
                    "'.Mage::getModel('core/url')->getUrl('udropship/index/vendorAutocomplete').'",
                    {
                        paramName: "vendor_name",
                        method: "get",
                        minChars: 2,
                        updateElement: function(el) {
                            $("'.$this->_getHtmlId().'").value = el.title;
                            $("_autocomplete_'.$this->_getHtmlId().'").value = el.innerHTML.stripTags();
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
            </script>        	
            ';
            self::$_gridInit = true;
        } else {
            $html = parent::getHtml();
        }
        return $html;
    }

}
