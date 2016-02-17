<?php
/**
 * Created by PhpStorm.
 * User: Vu Van Phan
 * Date: 23/01/2015
 * Time: 23:35
 */
class Sm_Cameraslide_Helper_Admin extends Mage_Core_Helper_Abstract
{
    /*
        Chek permission for passed action

        @param string $action
        @return bool
    */
    public function isActionAllowed($action)
    {
        return Mage::getSingleton('admin/session')->isAllowed('cameraslide/camerasliders/'.$action);
    }
}