<?php

class Unirgy_DropshipVacation_Model_Observer
{
    public function udropship_adminhtml_vendor_edit_prepare_form($observer)
    {
        $form = $observer->getForm();
        $vForm = $form->getElement('vendor_form');
        if ($vForm) {
            $hlp = Mage::helper('udropship');
            $vForm->addType('vacation_mode', Mage::getConfig()->getBlockClassName('udropship/adminhtml_vendor_helper_form_dependSelect'));
            $vForm->addField('vacation_mode', 'vacation_mode', array(
                'name'      => 'vacation_mode',
                'label'     => Mage::helper('udropship')->__('Vacation Mode'),
                'options'   => Mage::getSingleton('udvacation/source')->setPath('vacation_mode')->toOptionHash(),
                'field_config' => array(
                    'depend_fields' => array(
                        'vacation_end' => '1,2',
                    )
                )
            ));
            $vForm->addField('vacation_end', 'date', array(
                'name'      => 'vacation_end',
                'image' => Mage::getDesign()->getSkinUrl('images/grid-cal.gif'),
                'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
                'format' => Varien_Date::DATE_INTERNAL_FORMAT,
                'label'     => Mage::helper('udropship')->__('Vacation Ends At'),
            ));
        }
    }
    public function udropship_vendor_front_preferences($observer)
    {
        $data = $observer->getEvent()->getData();
        $data['fieldsets']['account']['fields']['vacation_mode'] = array(
            'position' => 11,
            'name' => 'vacation_mode',
            'type' => 'select',
            'label' => 'Vacation Mode',
            'options' => Mage::getSingleton('udvacation/source')->setPath('vacation_mode')->toOptionArray(),
        );
        $data['fieldsets']['account']['fields']['vacation_end'] = array(
            'position' => 12,
            'name' => 'vacation_end',
            'type' => 'date',
            'label' => 'Vacation Ends At',
        );
    }
    public function udropship_vendor_preferences_save_before($observer)
    {
        $data = $observer->getEvent()->getData();
        $v = $data['vendor'];
        $p = $data['post_data'];
        foreach (array(
            'vacation_mode', 'vacation_end'
        ) as $f) {
            $v->setData($f, @$p[$f]);
        }
    }
    public function udropship_vendor_save_commit_after($observer)
    {
        $vendor = $observer->getVendor();
        Mage::helper('udvacation')->processVendorChange($vendor);
    }

    public function udropship_prepare_quote_items_after($observer)
    {
        foreach ($observer->getItems() as $item) {
            $iVendor = Mage::helper('udropship')->getVendor($item->getUdropshipVendor());
            if (Unirgy_DropshipVacation_Model_Source::MODE_VACATION_NOTIFY == $iVendor->getData('vacation_mode')) {
                if (($message = Mage::getStoreConfig('udropship/customer/vacation_message'))) {
                    if ($iVendor->getData('vacation_end')) {
                        $now = Mage::app()->getLocale()->date(
                            null,
                            null,
                            null,
                            false
                        );
                        $vacationEnd = Mage::app()->getLocale()->date(
                            strtotime($iVendor->getData('vacation_end')),
                            null,
                            null,
                            false
                        );
                        if ($now->compare($vacationEnd)==-1) {
                            $message = str_replace('{{vacation_end}}', $vacationEnd->toString(Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM)), $message);
                            $item->setMessage($message);
                        }
                    }
                }
            }
        }
    }

    public function processVacations()
    {
        $vendors = Mage::getModel('udropship/vendor')->getCollection();
        $vacDisabled = Unirgy_DropshipVacation_Model_Source::MODE_VACATION_DISABLE;
        $vacNotify = Unirgy_DropshipVacation_Model_Source::MODE_VACATION_NOTIFY;
        $vacNo = Unirgy_DropshipVacation_Model_Source::MODE_NOT_VACATION;
        $vacationEnd = Mage::app()->getLocale()->storeDate();
        $vendors->getSelect()->where("vacation_mode in (?)", array($vacDisabled,$vacNotify));
        $vendors->getSelect()->where("vacation_end<?", $vacationEnd->toString(Varien_Date::DATE_INTERNAL_FORMAT));
        foreach ($vendors as $vendor) {
            $wasDisabled = $vendor->getData('vacation_mode')==$vacDisabled;
            $vendor->setData('vacation_mode', $vacNo);
            if ($wasDisabled) Mage::helper('udvacation')->processVendorChange($vendor);
            $vendor->setData('vacation_end', '');
            Mage::getResourceSingleton('udropship/helper')->updateModelFields($vendor, 'vacation_mode');
        }
    }

}