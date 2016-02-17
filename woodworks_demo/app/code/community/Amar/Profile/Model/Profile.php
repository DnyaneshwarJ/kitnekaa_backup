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
class Amar_Profile_Model_Profile extends Mage_Core_Model_Abstract
{
    //put your code here
    protected $_resourceModel = "profile/profile";
    
    protected function _construct() {
        $this->_init($this->_resourceModel);
    }
}

?>
