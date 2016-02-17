<?php
/* @var $installer Mage_Customer_Model_Entity_Setup */
$installer = new Mage_Eav_Model_Entity_Setup('core_setup');
$installer->startSetup();
$customers = Mage::getModel('customer/customer')->getCollection();

foreach($customers as $customer)
{
  $current_cust=Mage::getModel('customer/customer')
                ->setWebsiteId(Mage::app()->getWebsite()->getId())
                ->load($customer->getId());
    $current_cust->setId($customer->getId())
                ->setCompanyId(null)->save();

}

/** @var $coreResource Mage_Core_Model_Resource */
$coreResource = Mage::getSingleton('core/resource');

/** @var $conn Varien_Db_Adapter_Pdo_Mysql */
$conn = $coreResource->getConnection('core_read');

$conn->delete(
    $coreResource->getTableName('users/company'));

$installer->endSetup();