<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    Unirgy_Dropship
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

class Unirgy_Dropship_Helper_Error extends Mage_Core_Helper_Abstract
{
    public function sendPollTrackingFailedNotification($tracks, $error, $storeId)
    {
        $store = Mage::app()->getStore($storeId);

        if (!$store->getConfig('udropship/error_notifications/enabled') || empty($tracks)) {
            return $this;
        }

        $subject  = $store->getConfig('udropship/error_notifications/poll_tracking_failed_subject');
        $template = $store->getConfig('udropship/error_notifications/poll_tracking_failed_template');
        $to       = $store->getConfig('udropship/error_notifications/receiver');
        $from     = $store->getConfig('udropship/error_notifications/sender');

        $trackingIds = array();
        $orderIds = array();
        $shipmentIds = array();
        foreach ($tracks as $track) {
            $trackingIds[$track->getNumber()] = $track->getNumber();
            $shipmentIds[$track->getShipment()->getIncrementId()] = $track->getShipment()->getIncrementId();
            $orderIds[$track->getShipment()->getOrder()->getIncrementId()] = $track->getShipment()->getOrder()->getIncrementId();
        }

        $ahlp = Mage::getModel('adminhtml/url');

        if ($subject && $template) {
            $toEmail = $store->getConfig('trans_email/ident_'.$to.'/email');
            $toName = $store->getConfig('trans_email/ident_'.$to.'/name');
            $fromEmail = $store->getConfig('trans_email/ident_'.$from.'/email');
            $fromName = $store->getConfig('trans_email/ident_'.$from.'/name');
            $data = array(
                'tracking_ids'  => implode("\n", $trackingIds),
                'order_ids'     => implode("\n", $orderIds),
                'shipment_ids'  => implode("\n", $shipmentIds),
                'error'         => $error,
            );
            foreach ($data as $k=>$v) {
                $subject = str_replace('{{'.$k.'}}', $v, $subject);
                $template = str_replace('{{'.$k.'}}', $v, $template);
            }

            $mail = Mage::getModel('core/email')
                ->setFromEmail($fromEmail)
                ->setFromName($fromName)
                ->setToEmail($toEmail)
                ->setToName($toName)
                ->setSubject($subject)
                ->setBody($template)
                ->send();
            //mail('"'.$toName.'" <'.$toEmail.'>', $subject, $template, 'From: "'.$vendor->getVendorName().'" <'.$vendor->getEmail().'>');
        }
    }
    public function sendPollTrackingLimitExceededNotification($tracks, $storeId)
    {
        $store = Mage::app()->getStore($storeId);

        if (!$store->getConfig('udropship/error_notifications/enabled') || empty($tracks)) {
            return $this;
        }

        $limit    = $store->getConfig('udropship/error_notifications/poll_tracking_limit');
        $subject  = $store->getConfig('udropship/error_notifications/poll_tracking_limit_exceeded_subject');
        $template = $store->getConfig('udropship/error_notifications/poll_tracking_limit_exceeded_template');
        $to       = $store->getConfig('udropship/error_notifications/receiver');
        $from     = $store->getConfig('udropship/error_notifications/sender');

        $trackingIds = array();
        $orderIds = array();
        $shipmentIds = array();
        foreach ($tracks as $track) {
            $trackingIds[$track->getNumber()] = $track->getNumber();
            $shipmentIds[$track->getShipment()->getIncrementId()] = $track->getShipment()->getIncrementId();
            $orderIds[$track->getShipment()->getOrder()->getIncrementId()] = $track->getShipment()->getOrder()->getIncrementId();
        }

        $ahlp = Mage::getModel('adminhtml/url');

        if ($subject && $template) {
            $toEmail = $store->getConfig('trans_email/ident_'.$to.'/email');
            $toName = $store->getConfig('trans_email/ident_'.$to.'/name');
            $fromEmail = $store->getConfig('trans_email/ident_'.$from.'/email');
            $fromName = $store->getConfig('trans_email/ident_'.$from.'/name');
            $data = array(
                'tracking_ids'  => implode("\n", $trackingIds),
                'order_ids'     => implode("\n", $orderIds),
                'shipment_ids'  => implode("\n", $shipmentIds),
                'limit'         => $limit,
            );
            foreach ($data as $k=>$v) {
                $subject = str_replace('{{'.$k.'}}', $v, $subject);
                $template = str_replace('{{'.$k.'}}', $v, $template);
            }

            $mail = Mage::getModel('core/email')
                ->setFromEmail($fromEmail)
                ->setFromName($fromName)
                ->setToEmail($toEmail)
                ->setToName($toName)
                ->setSubject($subject)
                ->setBody($template)
                ->send();
            //mail('"'.$toName.'" <'.$toEmail.'>', $subject, $template, 'From: "'.$vendor->getVendorName().'" <'.$vendor->getEmail().'>');
        }

        return $this;
    }
    public function sendLabelRequestFailedNotification($shipment, $error)
    {
        $order = $shipment->getOrder();
        $store = $order->getStore();

        if (!$store->getConfig('udropship/error_notifications/enabled')) {
            return $this;
        }

        $subject  = $store->getConfig('udropship/error_notifications/label_request_failed_subject');
        $template = $store->getConfig('udropship/error_notifications/label_request_failed_template');
        $to       = $store->getConfig('udropship/error_notifications/receiver');
        $from     = $store->getConfig('udropship/error_notifications/sender');

        $vendor = Mage::helper('udropship')->getVendor($shipment->getUdropshipVendor());
        $ahlp = Mage::getModel('adminhtml/url');

        if ($subject && $template && $vendor->getId()) {
            $toEmail = $store->getConfig('trans_email/ident_'.$to.'/email');
            $toName = $store->getConfig('trans_email/ident_'.$to.'/name');
            $fromEmail = $store->getConfig('trans_email/ident_'.$from.'/email');
            $fromName = $store->getConfig('trans_email/ident_'.$from.'/name');
            $data = array(
                'vendor_name'   => $vendor->getVendorName(),
                'order_id'      => $order->getIncrementId(),
                'shipment_id'   => $shipment->getIncrementId(),
                'vendor_url'    => $ahlp->getUrl('adminhtml/udropshipadmin_vendor/edit', array(
                    'id'        => $vendor->getId()
                )),
                'order_url'     => $ahlp->getUrl('adminhtml/sales_order/view', array(
                    'order_id'  => $order->getId()
                )),
                'shipment_url'  => $ahlp->getUrl('adminhtml/sales_order_shipment/view', array(
                    'shipment_id'=> $shipment->getId(),
                    'order_id'  => $order->getId(),
                )),
                'error'      => $error,
            );
            if (Mage::helper('udropship')->isUdpoActive() && ($po = Mage::helper('udpo')->getShipmentPo($shipment))) {
                $data['po_id'] = $po->getIncrementId();
                $data['po_url'] = $ahlp->getUrl('adminhtml/udpoadmin_order_po/view', array(
                    'udpo_id'  => $po->getId(),
                    'order_id' => $order->getId(),
                ));
                $template = preg_replace('/{{isPoAvailable}}(.*?){{\/isPoAvailable}}/s', '\1', $template);
            } else {
                $template = preg_replace('/{{isPoAvailable}}.*?{{\/isPoAvailable}}/s', '', $template);
            }
            foreach ($data as $k=>$v) {
                $subject = str_replace('{{'.$k.'}}', $v, $subject);
                $template = str_replace('{{'.$k.'}}', $v, $template);
            }

            $mail = Mage::getModel('core/email')
                ->setFromEmail($fromEmail)
                ->setFromName($fromName)
                ->setToEmail($toEmail)
                ->setToName($toName)
                ->setSubject($subject)
                ->setBody($template)
                ->send();
            //mail('"'.$toName.'" <'.$toEmail.'>', $subject, $template, 'From: "'.$vendor->getVendorName().'" <'.$vendor->getEmail().'>');
        }

        return $this;
    }
}
