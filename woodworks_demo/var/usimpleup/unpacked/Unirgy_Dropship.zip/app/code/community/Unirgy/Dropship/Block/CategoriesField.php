<?php

class Unirgy_Dropship_Block_CategoriesField extends Varien_Data_Form_Element_Abstract
{
	public function getElementHtml()
    {
        $catEl = Mage::getSingleton('core/layout')->createBlock('udropship/categories');
        $value = $this->getValue();
        if (is_array($value)) {
            $value = implode(',', $value);
        }
        $catEl->setForcedIdsString($value);
        $catEl
            ->setNameName($this->getName())
            ->setIdName($this->getId())
            ->setUseDeferToBootstrap(true);
        $html = $catEl->toHtml();
        $html.= $this->getAfterElementHtml();
        return $html;
    }
}