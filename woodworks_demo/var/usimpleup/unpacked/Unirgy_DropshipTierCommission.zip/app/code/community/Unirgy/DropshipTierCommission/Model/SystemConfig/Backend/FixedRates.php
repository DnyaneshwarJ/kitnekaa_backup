<?php


class Unirgy_DropshipTierCommission_Model_SystemConfig_Backend_FixedRates extends Mage_Adminhtml_Model_System_Config_Backend_Serialized
{
    protected function _beforeSave()
    {
        $udtcFixedConfig = $this->getValue();
        if (is_array($udtcFixedConfig) && !empty($udtcFixedConfig)
            && !empty($udtcFixedConfig['limit']) && is_array($udtcFixedConfig['limit'])
        ) {
            reset($udtcFixedConfig['limit']);
            $firstTitleKey = key($udtcFixedConfig['limit']);
            if (!is_numeric($firstTitleKey)) {
                $newudtcFixedConfig = array();
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                foreach ($udtcFixedConfig['limit'] as $_k => $_t) {
                    if ( ($_limit = $filter->filter($udtcFixedConfig['limit'][$_k]))
                        && false !== ($_value = $filter->filter($udtcFixedConfig['value'][$_k]))
                    ) {
                        $_limit = is_numeric($_limit) ? $_limit : '*';
                        $_sk    = is_numeric($_limit) ? $_limit : '9999999999';
                        $_sk    = 'str'.str_pad((string)$_sk, 20, '0', STR_PAD_LEFT);
                        $newudtcFixedConfig[$_sk] = array(
                            'limit' => $_limit,
                            'value' => $_value,
                        );
                    }
                }
                ksort($newudtcFixedConfig);
                $newudtcFixedConfig = array_values($newudtcFixedConfig);
                $this->setValue(array_values($newudtcFixedConfig));
            }
        }
        return parent::_beforeSave();
    }
}