 <?php 
 
class Unirgy_Dropship_Block_Adminhtml_Widget_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _getAdditionalElementTypes()
    {
        return array_merge(parent::_getAdditionalElementTypes(), array(
            'udropship_vendor'=>Mage::getConfig()->getBlockClassName('udropship/vendor_htmlselect')
        ));
    }
}