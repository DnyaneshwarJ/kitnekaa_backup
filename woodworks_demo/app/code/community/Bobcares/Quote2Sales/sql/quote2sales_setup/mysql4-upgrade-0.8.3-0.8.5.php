<?php

/**
 * Created on Jun 24th 2015
 *
 * @author Bobcares
 * @desc Adding necessary columns in product table
 */
echo 'Running Bobcares Quote2Sales upgrade from 0.8.3 to 0.8.5: ' . get_class($this) . "\n <br /> \n";

/* Creating the custom attribute to enable and disable reuest for quote option in the 
 * product 
 */
$customProductAttributeSetup = Mage::getResourceModel('catalog/setup', 'catalog_setup');
$customProductAttributeSetup->startSetup();
$customProductAttributeSetup->addAttribute('catalog_product', 'is_display_request_for_quote', array(
    'group' => 'General',
    'type' => 'int',
    'backend' => '',
    'frontend' => '',
    'label' => 'Display request for quote',
    'input' => 'boolean',
    'class' => '',
    'source' => '',
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible' => true,
    'required' => false,
    'user_defined' => true,
    'used_in_product_listing' => true,
    'default' => '',
    'searchable' => true,
    'filterable' => true,
    'comparable' => true,
    'visible_on_front' => true,
    'unique' => false,
    'apply_to' => 'simple,configurable,bundle,grouped,virtual,downloadable',
    'is_configurable' => true
));
$customProductAttributeSetup->endSetup();

echo "Done Running setup \n";

