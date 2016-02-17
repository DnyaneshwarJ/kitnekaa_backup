<?php
/**
 * Created by PhpStorm.
 * User: Vu Van Phan
 * Date: 27/01/2015
 * Time: 13:35
 */
class Sm_Cameraslide_Model_Resource_Sliders extends Mage_Core_Model_Resource_Db_Abstract{
    protected function _construct(){
        $this->_init('sm_cameraslide/sliders', 'sliders_id');
    }
}