<?php


class Unirgy_Dropship_Block_Adminhtml_Shipping_Edit_Tab_Titles
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    public function getTabLabel()
    {
        return Mage::helper('udropship')->__('Titles');
    }

    public function getTabTitle()
    {
        return Mage::helper('udropship')->__('Titles');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }

    protected function _prepareForm()
    {
        $shipping = Mage::registry('shipping_data');
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('shipping_');

        $fieldset = $form->addFieldset('default_title_fieldset', array(
            'legend' => Mage::helper('udropship')->__('Default Title')
        ));
        $titles = $shipping ? $shipping->getStoreTitles() : array();
        $fieldset->addField('store_default_title', 'text', array(
            'name'      => 'store_titles[0]',
            'required'  => false,
            'label'     => Mage::helper('udropship')->__('Default Title for All Store Views'),
            'value'     => isset($titles[0]) ? $titles[0] : '',
        ));

        $fieldset = $form->addFieldset('store_titles_fieldset', array(
            'legend'       => Mage::helper('udropship')->__('Store View Specific Title'),
            'table_class'  => 'form-list stores-tree',
        ));
        $renderer = $this->getLayout()->createBlock('udropship/adminhtml_storeSwitcher_formRenderer_fieldset');
        $fieldset->setRenderer($renderer);

        foreach (Mage::app()->getWebsites() as $website) {
            $fieldset->addField("w_{$website->getId()}_title", 'note', array(
                'label'    => $website->getName(),
                'fieldset_html_class' => 'website',
            ));
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                if (count($stores) == 0) {
                    continue;
                }
                $fieldset->addField("sg_{$group->getId()}_title", 'note', array(
                    'label'    => $group->getName(),
                    'fieldset_html_class' => 'store-group',
                ));
                foreach ($stores as $store) {
                    $fieldset->addField("s_{$store->getId()}", 'text', array(
                        'name'      => 'store_titles['.$store->getId().']',
                        'required'  => false,
                        'label'     => $store->getName(),
                        'value'     => isset($titles[$store->getId()]) ? $titles[$store->getId()] : '',
                        'fieldset_html_class' => 'store',
                    ));
                }
            }
        }

        $this->setForm($form);
        return parent::_prepareForm();
    }
}
