<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    Unirgy_Dropship
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

class Unirgy_Dropship_Block_Adminhtml_Vendor_Edit_Tab_Custom extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();
        $this->setDestElementId('vendor_custom');
        //$this->setTemplate('udropship/vendor/form.phtml');
    }

    protected function _prepareForm()
    {
        $vendor = Mage::registry('vendor_data');
        $hlp = Mage::helper('udropship');
        $id = $this->getRequest()->getParam('id');
        $form = new Varien_Data_Form();
        $this->setForm($form);

        $fieldset = $form->addFieldset('custom', array(
            'legend'=>Mage::helper('udropship')->__('Custom Vendor Information')
        ));

        $fieldset->addField('custom_data_combined', 'textarea', array(
            'name'      => 'custom_data_combined',
            'label'     => Mage::helper('udropship')->__('Custom Data'),
            'style'     => 'height:500px',
            'note'      => Mage::helper('udropship')->__("
Enter custom data for this vendor.<br/>
Each part should start with:<br/>
<pre>===== part_name =====</pre><br/>
Parts can be referenced from product template like this:
<xmp>
<?php echo Mage::helper('udropship')
  ->getVendor(\$_product)
    ->getData('part_name')?>
</xmp>
"
            ),
        ));

        if ($vendor) {
            $form->setValues($vendor->getData());
        }

        return parent::_prepareForm();
    }

}