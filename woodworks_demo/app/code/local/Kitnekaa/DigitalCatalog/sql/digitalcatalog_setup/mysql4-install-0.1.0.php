<?php
$installer = $this;
$installer->startSetup();
$parentId = '2';
 
$category = new Mage_Catalog_Model_Category();
$category->setName('Catalogs');
$category->setUrlKey('catalogs');
$category->setIsActive(1);
$category->setDisplayMode('PAGE');
$category->setIsAnchor(0);
 
$parentCategory = Mage::getModel('catalog/category')->load($parentId);
$category->setPath($parentCategory->getPath());               
 
$category->save();
unset($category);

$installer->endSetup();