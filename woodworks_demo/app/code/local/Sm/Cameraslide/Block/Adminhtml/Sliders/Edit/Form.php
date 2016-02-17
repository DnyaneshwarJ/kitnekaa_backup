<?php
/**
 * Created by PhpStorm.
 * User: Vu Van Phan
 * Date: 28-01-2015
 * Time: 16:44
 */
class Sm_Cameraslide_Block_Adminhtml_Sliders_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id' 		=> 'sliders_form',
            'action' 	=> $this->getUrl('*/*/saveSliders', $arrayName = array('id' => $this->getRequest()->getParam('id'))),
            'method'	=> 'post',
            'enctype'	=> 'multipart/form-data'
        ));

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}