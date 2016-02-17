<?php
/**
 * Created by PhpStorm.
 * User: Vu Van Phan
 * Date: 27/01/2015
 * Time: 13:36
 */
class Sm_Cameraslide_Model_Resource_Sliders_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract{

    /*
     * Define collection model
     * */
    protected function _construct(){
        $this->_init('sm_cameraslide/sliders');
    }

    public function addSlideFilter($slide)
    {
        if($slide instanceof Sm_Cameraslide_Model_Slide && $slide->getId())
        {
            $this->addFieldToFilter('slide_id', array(
                'eq'    => $slide->getId()
            ));
        }elseif(is_numeric($slide)){
            $this->addFieldToFilter('slide_id', array(
                'eq'    => $slide
            ));
        }elseif(is_array($slide)){
            $this->addFieldToFilter('slide_id', array(
                'in'    => $slide
            ));
        }
        return $this;
    }
}