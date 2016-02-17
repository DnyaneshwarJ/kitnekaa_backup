<?php


class Unirgy_DropshipTierShipping_Model_SystemConfig_Backend_DeliveryType extends Mage_Core_Model_Config_Data
{
    public function setValue($value)
    {
        if (is_array($value)) {
            unset($value['$ROW']);
        }
        $this->setData('value', $value);
        return $this;
    }
    protected function _beforeSave()
    {
        $value = $this->getValue();
        if (is_array($value)) {
            unset($value['$ROW']);
            $rHlp = Mage::getResourceSingleton('udropship/helper');
            $conn = $rHlp->getWriteConnection();
            $dtTable = $rHlp->getTable('udtiership/delivery_type');
            $fieldsData = $rHlp->myPrepareDataForTable($dtTable, array(), true);
            $fields = array_keys($fieldsData);
            $existing = $rHlp->loadDbColumns(Mage::getModel('udtiership/deliveryType'), true, $fields);
            $insert = array();
            foreach ($value as $v) {
                if (empty($v['delivery_title'])) continue;
                if (!empty($v['delivery_type_id'])) {
                    unset($existing[$v['delivery_type_id']]);
                } else {
                    $v['delivery_type_id'] = null;
                }
                $insert[] = $rHlp->myPrepareDataForTable($dtTable, $v, true);
            }
            if (!empty($insert)) {
                $rHlp->multiInsertOnDuplicate($dtTable, $insert);
            }
            if (!empty($existing)) {
                $conn->delete($dtTable, array('delivery_type_id in (?)'=>array_keys($existing)));
            }
        }
        $this->setValue('');
        return parent::_beforeSave();
    }

    protected function _afterLoad()
    {
        $rHlp = Mage::getResourceSingleton('udropship/helper');
        $conn = $rHlp->getWriteConnection();
        $dtTable = $rHlp->getTable('udtiership/delivery_type');
        $fieldsData = $rHlp->myPrepareDataForTable('udtiership/delivery_type', array(), true);
        $fields = array_keys($fieldsData);
        $existing = $rHlp->loadDbColumns(Mage::getModel('udtiership/deliveryType'), true, $fields);
        usort($existing, array($this, 'sortBySortOrder'));
        $this->setValue($existing);
    }

    public function sortBySortOrder($a, $b)
    {
        if (@$a['sort_order']<@$b['sort_order']) {
            return -1;
        } elseif (@$a['sort_order']>@$b['sort_order']) {
            return 1;
        }
        return 0;
    }
}
