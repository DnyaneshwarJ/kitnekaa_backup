<?php
/**
 * Created by PhpStorm.
 * User: Vu Van Phan
 * Date: 02-02-2015
 * Time: 14:08
 */
class Sm_Cameraslide_Model_Source_Slide
{
    public function toOptionArray()
    {
        $collection = Mage::getModel('sm_cameraslide/slide')->getCollection();
        $array      = array();
        foreach($collection as $slide)
        {
            $array[]    = array(
                'value' => $slide->getId(),
                'label' => $slide->getData('name_slide')
            );
        }
        return $array;
    }
}