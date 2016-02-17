<?php


class Unirgy_DropshipVendorProduct_Model_SystemConfig_Backend_TypeOfProduct extends Mage_Adminhtml_Model_System_Config_Backend_Serialized
{
    protected function _beforeSave()
    {
        $udprodTypeOfProduct = $this->getValue();
        if (is_array($udprodTypeOfProduct) && !empty($udprodTypeOfProduct)
            && !empty($udprodTypeOfProduct['type_of_product']) && is_array($udprodTypeOfProduct['type_of_product'])
        ) {
            reset($udprodTypeOfProduct['type_of_product']);
            $firstTitleKey = key($udprodTypeOfProduct['type_of_product']);
            if (!is_numeric($firstTitleKey)) {
                $newudprodTypeOfProduct = array();
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                foreach ($udprodTypeOfProduct['type_of_product'] as $_k => $_t) {
                    $newudprodTypeOfProduct[] = array(
                        'type_of_product' => $_t,
                        'attribute_set' => $udprodTypeOfProduct['attribute_set'][$_k],
                    );
                }
                $this->setValue($newudprodTypeOfProduct);
            }
        }
        return parent::_beforeSave();
    }
}
