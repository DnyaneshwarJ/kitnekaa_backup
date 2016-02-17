<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Attribute
 *
 * @author root
 */
class Amar_Profile_Model_Customer_Attribute extends Mage_Eav_Model_Entity_Attribute
{
    /**
     * 
     * @category this method returns the attribute collection of the cuzstomer only
     * @return Mage_Eav_Model_Resource_Entity_Attribute_Collection
     */
    
    
    public function _construct() {
        parent::_construct();
        $this->setEntityTypeId(Mage::getModel('eav/entity')->setType("customer")->getTypeId());
    }
    public function getCollection() 
    {
        $collection = $this->getResourceCollection()
                            ->addFieldToSelect('attribute_id','id')
                            ->addFieldToSelect('frontend_label')
                            ->addFieldToSelect('attribute_code')
                            ->addFieldToSelect('frontend_input')
                            ->addFieldToSelect('is_unique')
                            ->addFieldToSelect('is_required')
                            ->setEntityTypeFilter(1);
        return $collection;
    }
    
    
    public function loadByCode($code)
    {
        if($code != "")
            parent::loadByCode(1, $code);
        else
            $this->setEntityTypeId(Mage::getModel('eav/entity')->setType("customer")->getTypeId());
        return $this;
    }
    
}

?>
