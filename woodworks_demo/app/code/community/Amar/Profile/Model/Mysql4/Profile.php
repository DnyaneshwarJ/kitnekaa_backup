<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Profile
 *
 * @author root
 */
class Amar_Profile_Model_Mysql4_Profile extends Mage_Core_Model_Mysql4_Abstract
{
    //put your code here
    protected $mainTable = "profile/profile";
    protected $idFieldName = "id";
    
    
    protected function _construct() {
        $this->_init($this->mainTable, $this->idFieldName);
    }
}

?>
