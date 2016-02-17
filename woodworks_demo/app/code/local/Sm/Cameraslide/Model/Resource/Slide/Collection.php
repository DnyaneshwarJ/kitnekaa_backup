<?php
/**
 * Created by PhpStorm.
 * User: Vu Van Phan
 * Date: 24/01/2015
 * Time: 09:18
 */
class Sm_Cameraslide_Model_Resource_Slide_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract{

    /*
     * Define collection model
     * */
    protected function _construct(){
        $this->_init('sm_cameraslide/slide');
    }
}