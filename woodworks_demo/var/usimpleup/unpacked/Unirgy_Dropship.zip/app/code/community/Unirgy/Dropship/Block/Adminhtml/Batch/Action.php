<?php

class Unirgy_Dropship_Block_Adminhtml_Batch_Action extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $href = $this->getUrl('adminhtml/udropshipadmin_batch/batchLabels', array('batch_id'=>$row->getId()));
        return '<a href="'.$href.'">'.Mage::helper('udropship')->__('Download Labels').'</a>';
    }
}