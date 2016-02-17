<?php

class Unirgy_DropshipVendorPromotions_Model_RuleConditionProduct extends Mage_SalesRule_Model_Rule_Condition_Product
{
    public function getValueElementChooserUrl()
    {
        $isUdpromo = Mage::registry('is_udpromo_vendor');
        $url = '';
        switch ($this->getAttribute()) {
            case 'sku': case 'category_ids':
            if ($isUdpromo) {
                $url = 'udpromo/vendor/chooser'.'/attribute/'.$this->getAttribute();
            } else {
                $url = 'adminhtml/promo_widget/chooser'.'/attribute/'.$this->getAttribute();
            }
            if ($this->getJsFormObject()) {
                $url .= '/form/'.$this->getJsFormObject();
            }
            if ($isUdpromo) {
                $url = Mage::getUrl($url);
            } else {
                $url = Mage::helper('adminhtml')->getUrl($url);
            }
            break;
        }
        return $url;
    }
}