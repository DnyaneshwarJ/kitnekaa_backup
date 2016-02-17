<?php
$installer = new Mage_Eav_Model_Entity_Setup('core_setup');
$installer->startSetup();
$entity = $installer->getEntityTypeId('customer');

//if(!$installer->attributeExists($entity, 'mob_no_verification')) {
$installer->removeAttribute($entity, 'company');
//}

//if(!$installer->attributeExists($entity, 'otp_text')) {
$installer->removeAttribute($entity, 'company_id');
//}

$installer->addAttribute($entity, 'company_id', array(
    'type' => 'int',
    'label' => 'Company Id',
    'input' => 'text',
    'visible' => FALSE,
    'required' => FALSE
));

$oAttribute = Mage::getSingleton('eav/config')->getAttribute('customer', 'company_id');
$oAttribute->setData('used_in_forms', array('customer_account_edit','customer_account_create','adminhtml_customer','checkout_register'));
$oAttribute->save();
$installer->endSetup();