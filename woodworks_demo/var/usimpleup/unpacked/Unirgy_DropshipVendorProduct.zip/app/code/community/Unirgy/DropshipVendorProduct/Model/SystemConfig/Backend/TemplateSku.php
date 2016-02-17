<?php


class Unirgy_DropshipVendorProduct_Model_SystemConfig_Backend_TemplateSku extends Mage_Core_Model_Config_Data
{
    protected $_myOrigValue;
    public function setMyOrigValue($value)
    {
        $this->_myOrigValue = $value;
        return $this;
    }
    public function getMyOrigValue()
    {
        if (null === $this->_myOrigValue) {
            $origData = Mage::getResourceSingleton('udropship/helper')->loadDbColumns(
                Mage::getModel('core/config_data'),
                array('path'=>'udprod/template_sku/value'),
                array('value'),
                "scope='default' and scope_id=0"
            );
            $this->_myOrigValue = array();
            if (!empty($origData)) {
                reset($origData);
                $value = current($origData);
                $value = !isset($value['value']) ? array() : $value['value'];
                if (!is_array($value)) {
                    $value = unserialize($value);
                }
                $this->_myOrigValue = $value;
            }
        }
        return $this->_myOrigValue;
    }
    public function setValue($value)
    {
        $origValue = $this->getMyOrigValue();
        $origValue = empty($origValue) ? array() : $origValue;
        if (!is_array($origValue)) {
            $origValue = unserialize($origValue);
        }
        $value = empty($value) ? array() : $value;
        if (!is_array($value)) {
            $value = unserialize($value);
        }
        if (is_array($value)) {
            foreach ($value as $sIdEnc => $_val) {
                $sId = Mage::helper('udprod')->urlDecode($sIdEnc);
                if (is_array(@$_val['cfg_attributes_def'])) {
                    unset($_val['cfg_attributes_def']['$ROW']);
                    usort($_val['cfg_attributes_def'], array($this, 'sortBySortOrder'));
                    $cfgAttrs = array();
                    $iiAttrs = array();
                    foreach ($_val['cfg_attributes_def'] as $cad) {
                        $cfgAttrs[] = $cad['attribute_id'];
                        if ($cad['identify_image']) {
                            $iiAttrs[] = $cad['attribute_id'];
                        }
                    }
                    $_val['cfg_attributes'] = $cfgAttrs;
                    $_val['cfg_identify_image'] = $iiAttrs;
                    $origValue[$sId] = $_val;
                }
            }
        }
        $this->setData('value', $origValue);
        $this->setMyOrigValue($origValue);
        return $this;
    }
    protected function _afterLoad()
    {
        if (!is_array($this->getValue())) {
            $value = $this->getValue();
            $this->setData('value', empty($value) ? false : unserialize($value));
        }
    }

    protected function _beforeSave()
    {
        if (is_array($this->getValue())) {
            $this->setData('value', serialize($this->getValue()));
        }
    }

    public function sortBySortOrder($a, $b)
    {
        if ($a['sort_order']<$b['sort_order']) {
            return -1;
        } elseif ($a['sort_order']>$b['sort_order']) {
            return 1;
        }
        return 0;
    }
}
