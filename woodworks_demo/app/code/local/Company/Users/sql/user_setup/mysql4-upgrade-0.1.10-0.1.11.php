<?php
/* @var $installer Mage_Customer_Model_Entity_Setup */
$installer = new Mage_Eav_Model_Entity_Setup('core_setup');
$installer->startSetup();
$entity = $installer->getEntityTypeId('customer');
$data = array(
    'input' => 'text',
    'type' => 'text',
    'label' => 'Mobile No.',
    'frontend_label' => 'Mobile No.',
    'required' => true,
    'user_defined' => true,
    'visible' => true,
    'frontend_class' => 'kit-mob-number'
);
//$serializedData = serialize($data);
$installer->updateAttribute($entity, 'phoneno', $data);
$installer->endSetup();