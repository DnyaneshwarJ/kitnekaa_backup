<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Wishlist
 * @copyright  Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

/**
 * Create table 'notification/notification'
 */



$installer->run("
    DROP TABLE IF EXISTS notification;
    CREATE TABLE `notification` (
  `notification_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Notiifcation item ID',
  `sender_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Notification sender participant ID',
  `sender_type` enum('Buyer','Seller','Kitnekaa_user') DEFAULT NULL COMMENT 'Notification Sender Type',
  `receiver_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Notification Receiver participant ID',
  `receiver_type` enum('Buyer','Seller','Kitnekaa_user')  DEFAULT NULL COMMENT 'Notification recevier type',
  `store_id` smallint(5) unsigned DEFAULT NULL COMMENT 'notification sent for store_id',
  `added_date` timestamp NULL DEFAULT NULL COMMENT 'This field contain notifcation added date and time',
  `notification_msg` text COMMENT 'This field contain Notification message',
  `msg_type` enum('User','Product','Shopping List','Order','Quote','Sale','CRM','Other') DEFAULT NULL COMMENT 'This is message type based on message template will be displayed',			
  `notification_on` enum('Email','Phone','Both','None')  DEFAULT NULL COMMENT 'This will used to notify user in differnet mediums',
  `is_read` smallint(6) DEFAULT '0'COMMENT 'This will be updated when user will open the message from any device or mark as read',
  `status` smallint(6) DEFAULT '0',
	PRIMARY KEY (`notification_id`),
  KEY `IDX_NOTIFICATION_ID` (`notification_id`),
  KEY `IDX_NOTIFICATION_STORE_ID` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='User Notification messages';
  ");
$installer->endSetup();
