<?php

class Unirgy_Dropship_Model_SystemConfig_Backend_EndiciaPass extends Mage_Core_Model_Config_Data
{
    protected function _beforeSave()
    {
        $groups = $this->getGroups();
        $useGlobal = @$groups['general']['fields']['use_global']['value'];
        $endiciaCfg = new Varien_Object((array)@$groups['endicia']['fields']);
        $callEndiciaChangePass = true;
        foreach (array('endicia_requester_id', 'endicia_account_id', 'endicia_pass_phrase') as $eKey) {
            if (!$endiciaCfg->getData($eKey.'/value')) {
                $callEndiciaChangePass = false;
                break;
            }
        }
        $eNewPh = $endiciaCfg->getData('endicia_new_pass_phrase/value');
        $eNewPhC = $endiciaCfg->getData('endicia_new_pass_phrase_confirm/value');
        $callEndiciaChangePass = $callEndiciaChangePass && $eNewPh;
        if ($useGlobal && $callEndiciaChangePass) {
            if ((string)$eNewPh!=(string)$eNewPhC) {
                Mage::throwException('"Endicia New Pass Phrase" should match "Endicia Confirm New Pass Phrase"');
            }
            $vendor = Mage::getModel('udropship/vendor');
            $labelModel = Mage::helper('udropship')->getLabelCarrierInstance('usps')->setVendor($vendor);
            Mage::helper('udropship/label')->useGlobalSettings($vendor, 'usps');
            $labelModel->changePassPhrase($eNewPh);
            Mage::helper('udropship/label')->unUseGlobalSettings($vendor, 'usps');
            $this->setField('endicia_pass_phrase');
            $this->setPath(str_replace('endicia_new_pass_phrase', 'endicia_pass_phrase', $this->getPath()));
        } else {
            $this->setValue('');
        }
        return parent::_beforeSave();
    }
}