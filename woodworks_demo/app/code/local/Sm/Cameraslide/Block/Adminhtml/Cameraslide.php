<?php
/**
 * Created by PhpStorm.
 * User: Vu Van Phan
 * Date: 23/01/2015
 * Time: 23:25
 */
class Sm_Cameraslide_Block_Adminhtml_Cameraslide extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /*
     * Block construstor
     * */
    public function __construct()
    {
        $this->_controller = 'adminhtml_cameraslide';
        $this->_blockGroup = 'sm_cameraslide';
        $this->_headerText = "<i class='fa fa-folder-open'></i>".Mage::helper('sm_cameraslide')->__('Manager Slide');
        parent::__construct();
        if(Mage::helper('sm_cameraslide/admin')->isActionAllowed('save'))
        {
            $this->_updateButton('add', 'label',
                Mage::helper('sm_cameraslide')->__('Add Slide')
            );
        }else{
            $this->_removeButton('add');
        }
    }

    protected function _isAllowedAction( $action )
    {
        return true;
    }
}