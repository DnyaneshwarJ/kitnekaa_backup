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

class Unirgy_Dropship_Udropshipadmin_ShipmentController extends Mage_Adminhtml_Controller_Action
{
    public function shipAction()
    {
        $id = $this->getRequest()->getParam('id');
        $shipment = Mage::getModel('sales/order_shipment')->load($id);
        if ($shipment->getId()) {
            try {

                Mage::helper('udropship')->setShipmentComplete($shipment);
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('udropship')->__('Shipment has been marked as complete'));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('udropship')->__('There was a problem marking this shipment as complete: '.$e->getMessage()));
            }
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('udropship')->__('Invalid shipment ID supplied'));
        }

        $orderId = $this->getRequest()->getParam('order_id');
        $this->_redirect("adminhtml/sales_order_shipment/view/shipment_id/$id/order_id/$orderId");
    }
    
    protected function _initShipment()
    {
        $shipmentId = $this->getRequest()->getParam('shipment_id');
        $shipment = Mage::getModel('sales/order_shipment')->load($shipmentId);
        if (!$shipment->getId()) {
            $this->_getSession()->addError(Mage::helper('udropship')->__('This shipment no longer exists.'));
            $this->_redirect('*/*/');
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            return false;
        }
        Mage::register('current_shipment', $shipment);

        return $shipment;
    }
    
    public function addCommentAction()
    {
        try {
            $data = $this->getRequest()->getPost('comment');
            $shipment = $this->_initShipment();
            if (empty($data['comment']) && $data['status']==$shipment->getUdropshipStatus()) {
                Mage::throwException(Mage::helper('udropship')->__('Comment text field cannot be empty.'));
            }

            $hlp = Mage::helper('udropship');
            $status = $data['status'];
            
            $statusShipped   = Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_SHIPPED;
            $statusDelivered = Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_DELIVERED;
            $statusCanceled  = Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_CANCELED;
            $statuses = Mage::getSingleton('udropship/source')->setPath('shipment_statuses')->toOptionHash();
            
            $statusSaveRes = true;
            if ($status!=$shipment->getUdropshipStatus()) {
                $oldStatus = $shipment->getUdropshipStatus();
                if (($oldStatus==$statusShipped || $oldStatus==$statusDelivered) 
                    && $status!=$statusShipped && $status!=$statusDelivered && $hlp->isUdpoActive()
                ) {
                    Mage::helper('udpo')->revertCompleteShipment($shipment, true);
                } elseif ($oldStatus==$statusCanceled && $hlp->isUdpoActive()) {
                    Mage::throwException(Mage::helper('udropship')->__('Canceled shipment cannot be reverted'));
                }
                $changedComment = Mage::helper('udropship')->__("%s\n\n[%s has changed the shipment status to %s]", $data['comment'], 'Administrator', $statuses[$status]);
                $triedToChangeComment = Mage::helper('udropship')->__("%s\n\n[%s tried to change the shipment status to %s]", $data['comment'], 'Administrator', $statuses[$status]);
                if ($status==$statusShipped || $status==$statusDelivered) {
                    $hlp->completeShipment($shipment, true, $status==$statusDelivered);
                    $hlp->completeOrderIfShipped($shipment, true);
                    $hlp->completeUdpoIfShipped($shipment, true);
                    $commentText = $changedComment;
                } elseif ($status == $statusCanceled && $hlp->isUdpoActive()) {
                    if (Mage::helper('udpo')->cancelShipment($shipment, true)) {
                        $commentText = $changedComment;
                        Mage::helper('udpo')->processPoStatusSave(Mage::helper('udpo')->getShipmentPo($shipment), Unirgy_DropshipPo_Model_Source::UDPO_STATUS_PARTIAL, true, null);
                    } else {
                        $commentText = $triedToChangeComment;
                    }
                } else {
                    $shipment->setUdropshipStatus($status)->save();
                    $commentText = $changedComment;
                }
                $comment = Mage::getModel('sales/order_shipment_comment')
                    ->setComment($commentText)
                    ->setIsCustomerNotified(isset($data['is_customer_notified']))
                    ->setIsVendorNotified(isset($data['is_vendor_notified']))
                    ->setIsVisibleToVendor(isset($data['is_visible_to_vendor']))
                    ->setUsername(Mage::getSingleton('admin/session')->getUser()->getUsername())
                    ->setUdropshipStatus(@$statuses[$status]);
                $shipment->addComment($comment);
                if (isset($data['is_vendor_notified'])) {
                    Mage::helper('udropship')->sendShipmentCommentNotificationEmail($shipment, $data['comment']);
                    Mage::helper('udropship')->processQueue();
                }
                $shipment->sendUpdateEmail(!empty($data['is_customer_notified']), $data['comment']);
                $shipment->getCommentsCollection()->save();
            } else {
                $comment = Mage::getModel('sales/order_shipment_comment')
                    ->setComment($data['comment'])
                    ->setIsCustomerNotified(isset($data['is_customer_notified']))
                    ->setIsVendorNotified(isset($data['is_vendor_notified']))
                    ->setIsVisibleToVendor(isset($data['is_visible_to_vendor']))
                    ->setUsername(Mage::getSingleton('admin/session')->getUser()->getUsername())
                    ->setUdropshipStatus(@$statuses[$status]);
                $shipment->addComment($comment);
                if (isset($data['is_vendor_notified'])) {
                    Mage::helper('udropship')->sendShipmentCommentNotificationEmail($shipment, $data['comment']);
                    Mage::helper('udropship')->processQueue();
                }
                $shipment->sendUpdateEmail(!empty($data['is_customer_notified']), $data['comment']);
                $shipment->getCommentsCollection()->save();
            }

            $this->loadLayout();
            $response = $this->getLayout()->getBlock('order_comments')->toHtml();
        } catch (Mage_Core_Exception $e) {
            $response = array(
                'error'     => true,
                'message'   => $e->getMessage()
            );
            $response = Zend_Json::encode($response);
        } catch (Exception $e) {
            Mage::logException($e);
            $response = array(
                'error'     => true,
                'message'   => Mage::helper('udropship')->__('Cannot add new comment.')
            );
            $response = Zend_Json::encode($response);
        }
        $this->getResponse()->setBody($response);
    }

    public function resendPoAction(){
        $poIds = $this->getRequest()->getPost('shipment_ids');
        if (!empty($poIds)) {
            try {
                $pos = Mage::getResourceModel('sales/order_shipment_collection')
                    ->addAttributeToSelect('*')
                    ->addAttributeToFilter('entity_id', array('in' => $poIds))
                    ->load();

                foreach ($pos as $po) {
                    $po->afterLoad();
                    $po->setResendNotificationFlag(true);
                    Mage::helper('udropship')->sendVendorNotification($po);
                }
                Mage::helper('udropship')->processQueue();

                $this->_getSession()->addSuccess(Mage::helper('udropship')->__('%s notifications sent.', count($poIds)));
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::logException($e);
                $this->_getSession()->addError(Mage::helper('udropship')->__('Problems during notifications resend.'));
            }
        }
        $this->_redirect('adminhtml/sales_shipment/index');
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/shipment');
    }
    
}
