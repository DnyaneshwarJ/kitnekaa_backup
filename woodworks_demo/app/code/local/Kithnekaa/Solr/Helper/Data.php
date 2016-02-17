<?php

class Kithnekaa_Solr_Helper_Data extends Mage_Core_Helper_Abstract
{

    /**
     * @return string
     */
    public function getCategoriesDropdown() {
        
    	$categoriesArray = Mage::getModel('catalog/category')
		->getCollection()
		->addAttributeToSelect('name')
		->addAttributeToSort('path', 'asc')
		->addFieldToFilter('is_active', array('eq'=>'1'))
		->load()
		->toArray();
		
		
		foreach ($categoriesArray as $categoryId => $category) {
			if (isset($category['name'])) {
				$categories[] = array(
						'label' => $category['name'],
						'level'  =>$category['level'],
						'value' => $categoryId
				);
			}
		}
		
		return $categories;
    }

}