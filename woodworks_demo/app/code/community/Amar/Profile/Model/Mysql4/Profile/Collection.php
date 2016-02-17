<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Collection
 *
 * @author root
 */
class Amar_Profile_Model_Mysql4_Profile_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    //put your code here
    
    protected $model = "profile/profile";
    
    protected function _construct() {
        $this->_init($this->model);
    }
}

?>
