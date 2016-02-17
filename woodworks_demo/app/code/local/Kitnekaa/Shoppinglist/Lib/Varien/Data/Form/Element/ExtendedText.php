<?php
class Kitnekaa_Shoppinglist_Lib_Varien_Data_Form_Element_ExtendedText extends Varien_Data_Form_Element_Text{
	
public function getHtmlAttributes()
{
    
        return array('type', 'title', 'class', 'style', 'onclick', 'onchange', 'onkeyup', 'disabled', 'readonly', 'maxlength', 'tabindex','data-model','data-field','data-depend-on','data-depend-value');
}

}