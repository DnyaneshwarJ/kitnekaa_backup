<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Create
 *
 * @author root
 */
class Amar_Profile_Block_Customer_Account_Create extends Mage_Core_Block_Template
{
    //put your code here
    
    protected $_attributeCodes = array();
    
    /*
     * Array
(
    [website_id] => 1
    [entity_id] => 6
    [entity_type_id] => 1
    [attribute_set_id] => 0
    [email] => ram@laxman.com
    [group_id] => 1
    [increment_id] => 
    [store_id] => 1
    [created_at] => 2014-05-15T10:53:55-07:00
    [updated_at] => 2014-05-15 10:53:56
    [is_active] => 1
    [disable_auto_group_change] => 0
    [firstname] => Ram
    [lastname] => Laxman
    [password_hash] => 57578ce3dacad6d2c917c418ff1351fc:gIBdC68v81WlzykfTGxFQm0Y6TABrlWG
    [created_in] => English
    [exname] => AAACCC
    [flower] => 128
    [metal] => 131
    [yn] => 1
    [ydate] => 2014-07-18 00:00:00
    [ytestarea] => SURESH
    [tax_class_id] => 3
)
     */
    protected $_customerData = array();
    
    protected $_idPrefix = "";
    protected $_namePrefix = "";
    protected $_nameSufix = "";


    public function __construct()
    {
        parent::__construct();
        
        $this->_attributeCodes = $this->fetchAttributes();
        $this->_customerData = $this->getCustomerData();
    }
    
    protected function _beforeToHtml() {
        $mode= $this->getMode();
        
        if($mode != "")
        {
            $this->_idPrefix = $mode.':';
            $this->_namePrefix = $mode.'[';
            $this->_nameSufix = ']';
        }
        
        /*if($this->getStep() == "onepage")
        {
            $checkout = Mage::getSingleton('checkout/session')->getQuote();
            $billAddressData = $checkout->getBillingAddress()->getData();
            
            foreach($billAddressData as $key=> $value)
            {
                if($value == "")
                {
                    $billAddressData[$key] = $this->_customerData[$key];
                }
            }
            $this->_customerData = $billAddressData;
        }*/
        
        parent::_beforeToHtml();
    }

    public function getCustomerData()
    {
        if(Mage::getSingleton("customer/session")->isLoggedIn())
        {
            return Mage::getSingleton("customer/session")->getCustomer()->getData();
        }
    }
    
    
    
    protected function fetchAttributes()
    {
        $attributeCodes = array();
        
        $profileAttributeCollection = Mage::getModel("profile/profile")->getCollection()->setOrder('sort_order','asc');
        
        foreach($profileAttributeCollection as $profile_id => $_profile)
        {
            $attributeCodes[] = $_profile->getAttributeCode();
        }
        
        return $attributeCodes;
        
    }
    
    
    public function getAttributeCodes()
    {
        if(empty($this->_attributeCodes))
        {
            $this->_attributeCodes = $this->fetchAttributes();
        }
        
        return $this->_attributeCodes;
        
    }
    
    /*
     * Array
        (
            [attribute_id] => 962
            [entity_type_id] => 1
            [attribute_code] => flower
            [attribute_model] => 
            [backend_model] => 
            [backend_type] => int
            [backend_table] => 
            [frontend_model] => 
            [frontend_input] => select
            [frontend_label] => Flower
            [frontend_class] => 
            [source_model] => eav/entity_attribute_source_table
            [is_required] => 0
            [is_user_defined] => 1
            [default_value] => 127
            [is_unique] => 0
            [note] => 
            [is_visible] => 1
            [input_filter] => 
            [multiline_count] => 1
            [validate_rules] => 
            [is_system] => 0
            [sort_order] => 0
            [data_model] => 
        )
     */
    public function getAttributeFrontendHtmlControl($attributeCode)
    {
        $attribute = Mage::getModel("profile/customer_attribute")->loadByCode($attributeCode);
        
        $html  ='<label for="'.$this->_idPrefix.$attribute->getAttributeCode().'" class="'.(($attribute->getIsRequired() == true)?"required":"").'">';
        $html .=    (($attribute->getIsRequired() == true)?"<em>*</em>":"").$this->__($attribute->getFrontendLabel());
        $html .='</label>';
        $html .='<div class="input-box">';
        
        if($attribute->getFrontendInput() == 'select')
        {
            $html .= '  <select name="'.$this->_namePrefix.$attribute->getAttributeCode().$this->_nameSufix.'" id="'.$this->_idPrefix.$attribute->getAttributeCode().'" class="'.$attribute->getValidationRules().' '.(($attribute->getIsRequired() == true)?"required-entry":"").'">';
            $html .='        <option value="">'.$this->__('Choose Option..').'</option>';
                            $options = $attribute->getSource()->getAllOptions(false);
                            foreach($options as $_option)
                            {
                                $html .='<option value="'.$_option['value'].'" '.(((($this->_customerData[$attribute->getAttributeCode()] == "")?$attribute->getDefaultValue():$this->_customerData[$attribute->getAttributeCode()]) == $_option['value'])?"selected=\"selected\"":"").'>'.$this->__($_option['label']).'</option>';
                            }
            $html .='   </select>';
        }
        elseif($attribute->getFrontendInput() == 'boolean')
        {
            $html .= '  <select name="'.$this->_namePrefix.$attribute->getAttributeCode().$this->_nameSufix.'" id="'.$this->_idPrefix.$attribute->getAttributeCode().'" class="'.$attribute->getValidationRules().' '.(($attribute->getIsRequired() == true)?"required-entry":"").'">';
            $html .='        <option value="">'.$this->__('Choose Option..').'</option>';
                            $options = $attribute->getSource()->getAllOptions(false);
                            foreach($options as $_option)
                            {
                                $html .='<option value="'.$_option['value'].'" '.(((($this->_customerData[$attribute->getAttributeCode()] == "")?$attribute->getDefaultValue():$this->_customerData[$attribute->getAttributeCode()]) == $_option['value'])?"selected=\"selected\"":"").'>'.$this->__($_option['label']).'</option>';
                            }
            $html .='   </select>';
        }
        elseif($attribute->getFrontendInput() == "date")
        {
            if(Mage::registry("calender_added") == null)
            {
                $html .='<link rel="stylesheet" type="text/css" href="'.Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS).'/calendar/calendar-win2k-1.css" />
                    <script type="text/javascript" src="'.Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS).'/calendar/calendar.js"></script>
                    <script type="text/javascript" src="'.Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS).'/calendar/calendar-setup.js"></script>';

                $html .= $this->getLayout()->createBlock(
                    'Mage_Core_Block_Html_Calendar',
                    'html_calendar',
                    array('template' => 'page/js/calendar.phtml')
                )->toHtml();
                Mage::register("calender_added",1);
                
            }
            
            
            $html .='<input type="text" style="width:110px !important;" class="'.$attribute->getValidationRules().' '.(($attribute->getIsRequired() == true)?"required-entry":"").' input-text" value="'.date('m/d/Y',strtotime((($this->_customerData[$attribute->getAttributeCode()] == "")?$attribute->getDefaultValue():$this->_customerData[$attribute->getAttributeCode()]))).'" id="'.$this->_idPrefix.$attribute->getAttributeCode().'" name="'.$this->_namePrefix.$attribute->getAttributeCode().$this->_nameSufix.'"> 
                     <img style="" title="'.$this->__('Select Date').'" id="'.$attribute->getAttributeCode().'_trig" class="v-middle" alt="" src="'.Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN).'/adminhtml/default/default/images/grid-cal.gif">
                     <script type="text/javascript">
                     //&lt;![CDATA[
                     document.observe("dom:loaded",function(){
                            setTimeout(function(){
                                    Calendar.setup({
                                        inputField: "'.$attribute->getAttributeCode().'",
                                        ifFormat: "%m/%e/%Y",
                                        showsTime: false,
                                        button: "'.$attribute->getAttributeCode().'_trig",
                                        align: "Bl",
                                        singleClick : true,
                                        date :new Date()
                                    });
                            },3000);
                        });
                     //]]&gt;
                     </script>';
            
        }
        elseif($attribute->getFrontendInput() == "textarea")
        {
            $html.='<textarea cols="15" rows="2" class="'.$attribute->getValidationRules().' '.(($attribute->getIsRequired() == true)?"required-entry":"").' textarea" name="'.$this->_namePrefix.$attribute->getAttributeCode().$this->_nameSufix.'" id="'.$this->_idPrefix.$attribute->getAttributeCode().'">'.(($this->_customerData[$attribute->getAttributeCode()] == "")?$attribute->getDefaultValue():$this->_customerData[$attribute->getAttributeCode()]).'</textarea>';
        }
        elseif($attribute->getFrontendInput() == "text")
        {
            $html .='<input type="text" class="'.$attribute->getValidationRules().' '.(($attribute->getIsRequired() == true)?"required-entry":"").' input-text" value="'.(($this->_customerData[$attribute->getAttributeCode()] == "")?$attribute->getDefaultValue():$this->_customerData[$attribute->getAttributeCode()]).'" name="'.$this->_namePrefix.$attribute->getAttributeCode().$this->_nameSufix.'" id="'.$this->_idPrefix.$attribute->getAttributeCode().'">';
        }
        $html .='</div>';
        
        return $html;
    }
    
    
}

?>
