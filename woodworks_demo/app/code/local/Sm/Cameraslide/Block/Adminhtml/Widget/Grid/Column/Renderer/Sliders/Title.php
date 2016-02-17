<?php
/**
 * Created by PhpStorm.
 * User: Vu Van Phan
 * Date: 04-02-2015
 * Time: 8:47
 */
class Sm_Cameraslide_Block_Adminhtml_Widget_Grid_Column_Renderer_Sliders_Title extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $params = Mage::helper('core')->jsonDecode($row->getParams());
        if(isset($params['sliders_title']))
        {
            return '<h3>'.$params['sliders_title'].'</h3>';
        }else{
            return '';
        }
    }
}