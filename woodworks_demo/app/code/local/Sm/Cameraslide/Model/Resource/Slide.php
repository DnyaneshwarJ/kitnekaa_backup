<?php
/**
 * Created by PhpStorm.
 * User: Vu Van Phan
 * Date: 24/01/2015
 * Time: 09:18
 */
class Sm_Cameraslide_Model_Resource_Slide extends Mage_Core_Model_Resource_Db_Abstract{
    protected function _construct(){
        $this->_init('sm_cameraslide/slide', 'slide_id');
    }
}