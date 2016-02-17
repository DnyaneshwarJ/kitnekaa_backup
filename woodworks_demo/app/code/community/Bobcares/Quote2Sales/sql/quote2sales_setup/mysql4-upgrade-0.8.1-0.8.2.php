<?php

/**
 * Created on 19 Feb 2015
 *
 * @author Bobcares
 * @desc Creating necessary tables for quote2sales module
 */
echo 'Running Bobcares Quote2Sales upgrade from 0.8.1 to 0.8.2: ' . get_class($this) . "\n <br /> \n";
$installer = $this;
$installer->startSetup();
$installer->run("
CREATE TABLE IF NOT EXISTS `quote2sales_requests_status` (
    `status_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `request_id` int(11) unsigned DEFAULT NULL,
    `quote_id` int(11) unsigned DEFAULT NULL,
    `order_id` int(11) unsigned DEFAULT NULL,
    `status` varchar(50) DEFAULT NULL,  
     PRIMARY KEY (`status_id`)
    );
");

$requestModel = Mage::getModel('quote2sales/request');
$requestArray = $requestModel->getAllData();

//fetch each item 
foreach ($requestArray as $item) {
    $requestId = $item['request_id'];
    $quoteId = $item['quote_id'];
    $ordeId = $item["order_id"];
    $requestStatus = $item["status"];

    //If the status is not "waiting"  and not null then insert request status to table
    if ($requestStatus != "Waiting" && $requestStatus != NULL) {
        $requestModel->insertQuoteStatus($requestStatus, $requestId, $quoteId, $ordeId);
    }
}

$installer->run("ALTER TABLE `quote2sales_requests` DROP COLUMN quote_id;
                 ALTER TABLE `quote2sales_requests` DROP COLUMN order_id ;"
);
echo "Done Running setup \n";
$installer->endSetup();

