<?php
$installer = $this;
$installer->startSetup();
$entity = $installer->getEntityTypeId('customer');

//if(!$installer->attributeExists($entity, 'mob_no_verification')) {
    $installer->removeAttribute($entity, 'mob_no_verification');
//}

//if(!$installer->attributeExists($entity, 'otp_text')) {
    $installer->removeAttribute($entity, 'otp_text');
//}

$installer->addAttribute($entity, 'mob_no_verification', array(
    'type' => 'int',
    'label' => 'Required Mobile Number Verification',
    'input' => 'boolean',
    'visible' => TRUE,
    'required' => FALSE,
    'default_value' => 0,
    'adminhtml_only' => '1'
));

$installer->addAttribute($entity, 'otp_text', array(
    'type' => 'varchar',
    'label' => 'OTP',
    'input' => 'text',
    'visible' => FALSE,
    'required' => FALSE
));

$forms = array(
    'adminhtml_customer',
);
$attribute = Mage::getSingleton('eav/config')->getAttribute($installer->getEntityTypeId('customer'), 'mob_no_verification');
$attribute->setData('used_in_forms', $forms);
$attribute->save();

$installer->endSetup();