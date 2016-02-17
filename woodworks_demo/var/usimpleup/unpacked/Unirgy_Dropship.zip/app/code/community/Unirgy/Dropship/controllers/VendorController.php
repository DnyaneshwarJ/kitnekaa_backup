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

class Unirgy_Dropship_VendorController extends Unirgy_Dropship_Controller_VendorAbstract
{

    public function indexAction()
    {
        $_hlp = Mage::helper('udropship');
        if ($_hlp->isUdpoActive() && !$this instanceof Unirgy_DropshipPo_VendorController) {
            $this->_forward('index', 'vendor', 'udpo');
            return;
        }
        switch ($this->getRequest()->getParam('submit_action')) {
        case 'labelBatch':
        case Mage::helper('udropship')->__('Create and Download Labels Batch'):
            $this->_forward('labelBatch');
            return;

        case 'existingLabelBatch':
            $this->_forward('existingLabelBatch');
            return;

        case 'packingSlips':
        case Mage::helper('udropship')->__('Download Packing Slips'):
            $this->_forward('packingSlips');
            return;

        case 'updateShipmentsStatus':
            $this->_forward('updateShipmentsStatus');
            return;
        case 'udbatchExport':
            $this->_forward('exportShipments', 'vendor_batch', 'udbatch');
            return;
        }

        $this->_renderPage(null, 'dashboard');
    }

    public function loginAction()
    {
        if (Mage::getSingleton('udropship/session')->isLoggedIn()) {
            $this->_forward('index');
        } else {
            $ajax = $this->getRequest()->getParam('ajax');
            if ($ajax) {
                Mage::getSingleton('udropship/session')->addError(Mage::helper('udropship')->__('Your session has been expired. Please log in again.'));
            }
            $this->_renderPage($ajax ? 'udropship_vendor_login_ajax' : null);
        }
    }

    public function logoutAction()
    {
        $this->_getSession()->logout();
        $this->_redirect('udropship/vendor');
    }

    public function passwordAction()
    {
        $session = Mage::getSingleton('udropship/session');
        $hlp = Mage::helper('udropship');
        $confirm = $this->getRequest()->getParam('confirm');
        if ($confirm) {
            $vendor = Mage::getModel('udropship/vendor')->load($confirm, 'random_hash');
            if ($vendor->getId()) {
                Mage::register('reset_vendor', $vendor);
            } else {
                $session->addError(Mage::helper('udropship')->__('Invalid confirmation link'));
            }
        }
        $this->_renderPage();
    }

    public function passwordPostAction()
    {
        $session = Mage::getSingleton('udropship/session');
        $hlp = Mage::helper('udropship');
        try {
            $r = $this->getRequest();
            if (($confirm = $r->getParam('confirm'))) {
                $password = $r->getParam('password');
                $passwordConfirm = $r->getParam('password_confirm');
                $vendor = Mage::getModel('udropship/vendor')->load($confirm, 'random_hash');
                if (!$password || !$passwordConfirm || $password!=$passwordConfirm || !$vendor->getId()) {
                    $session->addError('Invalid form data');
                    $this->_redirect('*/*/password', array('confirm'=>$confirm));
                    return;
                }
                $vendor->setPassword($password)->unsRandomHash()->save();
                $session->loginById($vendor->getId());
                $session->addSuccess(Mage::helper('udropship')->__('Your password has been reset.'));
                $this->_redirect('*/*');
            } elseif (($email = $r->getParam('email'))) {
                $hlp->sendPasswordResetEmail($email);
                $session->addSuccess(Mage::helper('udropship')->__('Thank you, password reset instructions have been sent to the email you have provided, if a vendor with such email exists.'));
                $this->_redirect('*/*/login');
            } else {
                $session->addError(Mage::helper('udropship')->__('Invalid form data'));
                $this->_redirect('*/*/password');
            }
        } catch (Exception $e) {
            $session->addError($e->getMessage());
            $this->_redirect('*/*/password');
        }
    }

    public function accountAction()
    {
        $this->_renderPage();
    }

    public function preferencesAction()
    {
        if (Mage::helper('udropship')->isWysiwygAllowed()) {
            $this->_renderPage(array('default', 'uwysiwyg_editor', 'uwysiwyg_editor_js'), 'preferences');
        } else {
            $this->_renderPage(null, 'preferences');
        }
    }

    public function preferencesPostAction()
    {
        $defaultAllowedTags = Mage::getStoreConfig('udropship/vendor/preferences_allowed_tags');
        $session = Mage::getSingleton('udropship/session');
        $hlp = Mage::helper('udropship');
        $r = $this->getRequest();
        if ($r->isPost()) {
            $p = $r->getPost();
            $hlp->processPostMultiselects($p);
            try {
                $v = $session->getVendor();
                foreach (array(
                    'vendor_name', 'vendor_attn', 'email', 'password', 'telephone',
                    'street', 'city', 'zip', 'country_id', 'region_id', 'region',
                    'billing_vendor_attn', 'billing_email', 'billing_telephone',
                    'billing_street', 'billing_city', 'billing_zip', 'billing_country_id', 'billing_region_id', 'billing_region'
                ) as $f) {
                    if (array_key_exists($f, $p)) $v->setData($f, $p[$f]);
                }
                foreach (Mage::getConfig()->getNode('global/udropship/vendor/fields')->children() as $code=>$node) {
                    if (!isset($p[$code])) {
                        continue;
                    }
                    $param = $p[$code];
                    if (is_array($param)) {
                        foreach ($param as $key=>$val) {
                            $param[$key] = strip_tags($val, $defaultAllowedTags);
                        }
                    }
                    else {
                        $allowedTags = $defaultAllowedTags;
                        if ($node->filter_input && ($stripTags = $node->filter_input->strip_tags) && isset($stripTags->allowed)) {
                            $allowedTags = (string)$node->strip_tags->allowed;
                        }
                        if ($allowedTags && $node->type != 'wysiwyg') {
                            $param = strip_tags($param, $allowedTags);
                        }

                        if ($node->filter_input && ($replace = $node->filter_input->preg_replace) && isset($replace->from) && isset($replace->to)) {
                            $param = preg_replace((string)$replace->from, (string)$replace->to, $param);
                        }
                    } // end code injection protection
                    $v->setData($code, $param);
                }
                Mage::dispatchEvent('udropship_vendor_preferences_save_before', array('vendor'=>$v, 'post_data'=>&$p));
                $v->save();
#echo "<pre>"; print_r($v->debug()); exit;
                $session->addSuccess(Mage::helper('udropship')->__('Settings has been saved'));
            } catch (Exception $e) {
                $session->addError($e->getMessage());
            }
        }
        $this->_redirect('udropship/vendor/preferences');
    }

    public function productAction()
    {
        $this->_renderPage(null, 'stockprice');
    }

    public function productSaveAction()
    {
        $hlp = Mage::helper('udropship');
        $session = Mage::getSingleton('udropship/session');
        try {
            $cnt = $hlp->saveVendorProducts($this->getRequest()->getParam('vp'));
            if (($multi = Mage::getConfig()->getNode('modules/Unirgy_DropshipMulti')) && $multi->is('active')) {
                $cnt += Mage::helper('udmulti')->saveVendorProductsPidKeys($this->getRequest()->getParam('vp'));
            }
            if ($cnt) {
                $session->addSuccess(Mage::helper('udropship')->__($cnt==1 ? '%s product was updated' : '%s products were updated', $cnt));
            } else {
                $session->addNotice(Mage::helper('udropship')->__('No updates were made'));
            }
        } catch (Exception $e) {
            $session->addError($e->getMessage());
        }
        if (is_callable(array(Mage::helper('core/http', 'getHttpReferer')))) {
            $this->getResponse()->setRedirect(Mage::helper('core/http')->getHttpReferer());
        } else {
            $this->getResponse()->setRedirect(@$_SERVER['HTTP_REFERER']);
        }
    }

    public function batchesAction()
    {
        $this->_renderPage(null, 'batches');
    }

    public function shipmentInfoAction()
    {
        $this->_setTheme();
        $this->loadLayout(false);

        $block = $this->getLayout()->getBlock('info');
        Mage::helper('udropship')->applyItemRenderers('sales_order_shipment', $block, '/checkout/', false);
        if (($url = Mage::registry('udropship_download_url'))) {
            $block->setDownloadUrl($url);
        }
        $this->_initLayoutMessages('udropship/session');

        $this->getResponse()->setBody($block->toHtml());
    }

    public function shipmentPostAction()
    {
        $hlp = Mage::helper('udropship');
        $r = $this->getRequest();
        $id = $r->getParam('id');
        $shipment = Mage::getModel('sales/order_shipment')->load($id);
        $vendor = $hlp->getVendor($shipment->getUdropshipVendor());
        $session = $this->_getSession();

        if (!$shipment->getId()) {
            return;
        }

        try {
            $store = $shipment->getOrder()->getStore();

            $track = null;
            $highlight = array();

            $partial = $r->getParam('partial_availability');
            $partialQty = $r->getParam('partial_qty');

            $printLabel = $r->getParam('print_label');
            $number = $r->getParam('tracking_id');

            $carrier = $r->getParam('carrier');
            $carrierTitle = $r->getParam('carrier_title');

            $notifyOn = Mage::getStoreConfig('udropship/customer/notify_on', $store);
            $pollTracking = Mage::getStoreConfig('udropship/customer/poll_tracking', $store);
            $autoComplete = Mage::getStoreConfig('udropship/vendor/auto_shipment_complete', $store);

            $statusShipped = Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_SHIPPED;
            $statusDelivered = Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_DELIVERED;
            $statusCanceled = Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_CANCELED;
            $statuses = Mage::getSingleton('udropship/source')->setPath('shipment_statuses')->toOptionHash();
            // if label was printed
            if ($printLabel) {
                $status = $r->getParam('is_shipped') ? $statusShipped : Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_PARTIAL;
                $isShipped = $r->getParam('is_shipped') ? true : false;
            } else { // if status was set manually
                $status = $r->getParam('status');
                $isShipped = $status == $statusShipped || $status==$statusDelivered || $autoComplete && ($status==='' || is_null($status));
            }

            // if label to be printed
            if ($printLabel) {
                $data = array(
                    'weight'    => $r->getParam('weight'),
                    'value'     => $r->getParam('value'),
                    'length'    => $r->getParam('length'),
                    'width'     => $r->getParam('width'),
                    'height'    => $r->getParam('height'),
                    'reference' => $r->getParam('reference'),
                    'package_count' => $r->getParam('package_count'),
                );

                $extraLblInfo = $r->getParam('extra_label_info');
                $extraLblInfo = is_array($extraLblInfo) ? $extraLblInfo : array();
                $data = array_merge($data, $extraLblInfo);

                $oldUdropshipMethod = $shipment->getUdropshipMethod();
                $oldUdropshipMethodDesc = $shipment->getUdropshipMethodDescription();
                if ($r->getParam('use_method_code')) {
                    list($useCarrier, $useMethod) = explode('_', $r->getParam('use_method_code'), 2);
                    if (!empty($useCarrier) && !empty($useMethod)) {
                        $shipment->setUdropshipMethod($r->getParam('use_method_code'));
                        $carrierMethods = Mage::helper('udropship')->getCarrierMethods($useCarrier);
                        $shipment->setUdropshipMethodDescription(
                            Mage::getStoreConfig('carriers/'.$useCarrier.'/title', $shipment->getOrder()->getStoreId())
                            .' - '.$carrierMethods[$useMethod]
                        );
                    }
                }

                // generate label
                $batch = Mage::getModel('udropship/label_batch')
                    ->setVendor(Mage::getSingleton('udropship/session')->getVendor())
                    ->processShipments(array($shipment), $data, array('mark_shipped'=>$isShipped));

                // if batch of 1 label is successfull
                if ($batch->getShipmentCnt()) {
                    $url = Mage::getUrl('udropship/vendor/reprintLabelBatch', array('batch_id'=>$batch->getId()));
                    Mage::register('udropship_download_url', $url);

                    if (($track = $batch->getLastTrack())) {
                        $session->addSuccess('Label was succesfully created');
                        Mage::helper('udropship')->addShipmentComment(
                            $shipment,
                            Mage::helper('udropship')->__('%s printed label ID %s', $vendor->getVendorName(), $track->getNumber())
                        );
                        $shipment->save();
                        $highlight['tracking'] = true;
                    }
                    if ($r->getParam('use_method_code')) {
                        $shipment->setUdropshipMethod($oldUdropshipMethod);
                        $shipment->setUdropshipMethodDescription($oldUdropshipMethodDesc);
                        $shipment->getResource()->saveAttribute($shipment, 'udropship_method');
                        $shipment->getResource()->saveAttribute($shipment, 'udropship_method_description');
                    }
                } else {
                    if ($batch->getErrors()) {
                        foreach ($batch->getErrors() as $error=>$cnt) {
                            $session->addError(Mage::helper('udropship')->__($error, $cnt));
                        }
                        if ($r->getParam('use_method_code')) {
                            $shipment->setUdropshipMethod($oldUdropshipMethod);
                            $shipment->setUdropshipMethodDescription($oldUdropshipMethodDesc);
                            $shipment->getResource()->saveAttribute($shipment, 'udropship_method');
                            $shipment->getResource()->saveAttribute($shipment, 'udropship_method_description');
                        }
                    } else {
                        $session->addError(Mage::helper('udropship')->__('No items are available for shipment'));
                        if ($r->getParam('use_method_code')) {
                            $shipment->setUdropshipMethod($oldUdropshipMethod);
                            $shipment->setUdropshipMethodDescription($oldUdropshipMethodDesc);
                            $shipment->getResource()->saveAttribute($shipment, 'udropship_method');
                            $shipment->getResource()->saveAttribute($shipment, 'udropship_method_description');
                        }
                    }
                }

            } elseif ($number) { // if tracking id was added manually
                $method = explode('_', $shipment->getUdropshipMethod(), 2);
                $title = Mage::getStoreConfig('carriers/'.$method[0].'/title', $store);
                $_carrier = $method[0];
                if (!empty($carrier) && !empty($carrierTitle)) {
                    $_carrier = $carrier;
                    $title = $carrierTitle;
                }
                $track = Mage::getModel('sales/order_shipment_track')
                    ->setNumber($number)
                    ->setCarrierCode($_carrier)
                    ->setTitle($title);

                $shipment->addTrack($track);

                Mage::helper('udropship')->processTrackStatus($track, true, $isShipped);

                Mage::helper('udropship')->addShipmentComment(
                    $shipment,
                    Mage::helper('udropship')->__('%s added tracking ID %s', $vendor->getVendorName(), $number)
                );
                $shipment->save();
                $session->addSuccess(Mage::helper('udropship')->__('Tracking ID has been added'));

                $highlight['tracking'] = true;
            }

            // if track was generated - for both label and manual tracking id
            /*
            if ($track) {
                // if poll tracking is enabled for the vendor
                if ($pollTracking && $vendor->getTrackApi()) {
                    $track->setUdropshipStatus(Unirgy_Dropship_Model_Source::TRACK_STATUS_PENDING);
                    $isShipped = false;
                } else { // otherwise process track
                    $track->setUdropshipStatus(Unirgy_Dropship_Model_Source::TRACK_STATUS_READY);
                    Mage::helper('udropship')->processTrackStatus($track, true, $isShipped);
                }
            */
            // if tracking id added manually and new status is not current status
            $shipmentStatuses = false;
            if (Mage::getStoreConfig('udropship/vendor/is_restrict_shipment_status')) {
                $shipmentStatuses = Mage::getStoreConfig('udropship/vendor/restrict_shipment_status');
                if (!is_array($shipmentStatuses)) {
                    $shipmentStatuses = explode(',', $shipmentStatuses);
                }
            }
            if (!$printLabel && !is_null($status) && $status!=='' && $status!=$shipment->getUdropshipStatus()
                && (!$shipmentStatuses || (in_array($shipment->getUdropshipStatus(), $shipmentStatuses) && in_array($status, $shipmentStatuses)))
            ) {
                $oldStatus = $shipment->getUdropshipStatus();
                if (($oldStatus==$statusShipped || $oldStatus==$statusDelivered)
                    && $status!=$statusShipped && $status!=$statusDelivered && $hlp->isUdpoActive()
                ) {
                    Mage::helper('udpo')->revertCompleteShipment($shipment, true);
                } elseif ($oldStatus==$statusCanceled && $hlp->isUdpoActive()) {
                    Mage::throwException(Mage::helper('udropship')->__('Canceled shipment cannot be reverted'));
                }
                $changedComment = Mage::helper('udropship')->__('%s has changed the shipment status to %s', $vendor->getVendorName(), $statuses[$status]);
                $triedToChangeComment = Mage::helper('udropship')->__('%s tried to change the shipment status to %s', $vendor->getVendorName(), $statuses[$status]);
                if ($status==$statusShipped || $status==$statusDelivered) {
                    $hlp->completeShipment($shipment, true, $status==$statusDelivered);
                    $hlp->completeOrderIfShipped($shipment, true);
                    $hlp->completeUdpoIfShipped($shipment, true);
                    Mage::helper('udropship')->addShipmentComment(
                        $shipment,
                        $changedComment
                    );
                } elseif ($status == $statusCanceled && $hlp->isUdpoActive()) {
                    if (Mage::helper('udpo')->cancelShipment($shipment, true)) {
                        Mage::helper('udropship')->addShipmentComment(
                            $shipment,
                            $changedComment
                        );
                        Mage::helper('udpo')->processPoStatusSave(Mage::helper('udpo')->getShipmentPo($shipment), Unirgy_DropshipPo_Model_Source::UDPO_STATUS_PARTIAL, true, $vendor);
                    } else {
                        Mage::helper('udropship')->addShipmentComment(
                            $shipment,
                            $triedToChangeComment
                        );
                    }
                } else {
                    $shipment->setUdropshipStatus($status)->save();
                    Mage::helper('udropship')->addShipmentComment(
                        $shipment,
                        $changedComment
                    );
                }
                $shipment->getCommentsCollection()->save();
                $session->addSuccess(Mage::helper('udropship')->__('Shipment status has been changed'));
            }

            $comment = $r->getParam('comment');
            if ($comment || $partial=='inform' && $partialQty) {
                if ($partialQty) {
                    $comment .= "\n\nPartial Availability:\n";
                    foreach ($shipment->getAllItems() as $item) {
                        if (!array_key_exists($item->getId(), $partialQty) || '' === $partialQty[$item->getId()]) {
                            continue;
                        }
                        $comment .= Mage::helper('udropship')->__('%s x [%s] %s', $partialQty[$item->getId()], $item->getName(), $item->getSku())."\n";
                    }
                }

                Mage::helper('udropship')->sendVendorComment($shipment, $comment);
                $session->addSuccess(Mage::helper('udropship')->__('Your comment has been sent to store administrator'));

                $highlight['comment'] = true;
            }

            $deleteTrack = $r->getParam('delete_track');
            if ($deleteTrack) {
                $track = Mage::getModel('sales/order_shipment_track')->load($deleteTrack);
                if ($track->getId()) {

                    try {
                        $labelModel = Mage::helper('udropship')->getLabelCarrierInstance($track->getCarrierCode())->setVendor($vendor);
                        try {
                            $labelModel->voidLabel($track);
                            Mage::helper('udropship')->addShipmentComment(
                                $shipment,
                                Mage::helper('udropship')->__('%s voided tracking ID %s', $vendor->getVendorName(), $track->getNumber())
                            );
                            $session->addSuccess(Mage::helper('udropship')->__('Track %s was voided', $track->getNumber()));
                        } catch (Exception $e) {
                            Mage::helper('udropship')->addShipmentComment(
                                $shipment,
                                Mage::helper('udropship')->__('%s attempted to void tracking ID %s: %s', $vendor->getVendorName(), $track->getNumber(), $e->getMessage())
                            );
                            $session->addSuccess(Mage::helper('udropship')->__('Problem voiding track %s: %s', $track->getNumber(), $e->getMessage()));
                        }
                    } catch (Exception $e) {
                        // doesn't support voiding
                    }

                    $track->delete();
                    if ($track->getPackageCount()>1) {
                        foreach (Mage::getResourceModel('sales/order_shipment_track_collection')
                            ->addAttributeToFilter('master_tracking_id', $track->getMasterTrackingId())
                            as $_track
                        ) {
                            $_track->delete();
                        }
                    }
                    Mage::helper('udropship')->addShipmentComment(
                        $shipment,
                        Mage::helper('udropship')->__('%s deleted tracking ID %s', $vendor->getVendorName(), $track->getNumber())
                    );
                    $shipment->getCommentsCollection()->save();
                    #$save = true;
                    $highlight['tracking'] = true;
                    $session->addSuccess(Mage::helper('udropship')->__('Track %s was deleted', $track->getNumber()));
                } else {
                    $session->addError(Mage::helper('udropship')->__('Track %s was not found', $track->getNumber()));
                }
            }

            $session->setHighlight($highlight);
        } catch (Exception $e) {
            $session->addError($e->getMessage());
        }

        $this->_forward('shipmentInfo');
    }

    /**
    * Download one packing slip
    *
    */
    public function pdfAction()
    {
        try {
            $id = $this->getRequest()->getParam('shipment_id');
            if (!$id) {
                Mage::throwException('Invalid shipment ID is supplied');
            }

            $shipments = Mage::getResourceModel('sales/order_shipment_collection')
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('entity_id', $id)
                ->load();
            if (!$shipments->getSize()) {
                Mage::throwException(Mage::helper('udropship')->__('No shipments found with supplied IDs'));
            }

            return $this->_preparePackingSlips($shipments);

        } catch (Exception $e) {
            $this->_getSession()->addError(Mage::helper('udropship')->__($e->getMessage()));
        }
        $this->_redirect('udropship/vendor/');
    }

    /**
    * Download multiple packing slips
    *
    */
    public function packingSlipsAction()
    {
    	$result = array();
        try {
            $shipments = $this->getVendorShipmentCollection();
            if (!$shipments->getSize()) {
                Mage::throwException(Mage::helper('udropship')->__('No shipments found for these criteria'));
            }

            return $this->_preparePackingSlips($shipments);

        } catch (Exception $e) {
        	if ($this->getRequest()->getParam('use_json_response')) {
        		$result = array(
        			'error'=>true,
        			'message'=>$e->getMessage()
        		);
        	} else {
            	$this->_getSession()->addError(Mage::helper('udropship')->__($e->getMessage()));
        	}
        }
    	if ($this->getRequest()->getParam('use_json_response')) {
        	$this->getResponse()->setBody(
        		Mage::helper('core')->jsonEncode($result)
        	);
        } else {
        	$this->_redirect('udropship/vendor/', array('_current'=>true, '_query'=>array('submit_action'=>'')));
        }
    }

    /**
    * Generate and print labels batch
    *
    */
    public function labelBatchAction()
    {
    	$result = array();
        try {
            $shipments = $this->getVendorShipmentCollection();
            if (!$shipments->getSize()) {
                Mage::throwException(Mage::helper('udropship')->__('No shipments found for these criteria'));
            }

            Mage::getModel('udropship/label_batch')
                ->setVendor(Mage::getSingleton('udropship/session')->getVendor())
                ->processShipments($shipments, array(), array('mark_shipped'=>true))
                ->prepareLabelsDownloadResponse();

        } catch (Exception $e) {
        	if ($this->getRequest()->getParam('use_json_response')) {
        		$result = array(
        			'error'=>true,
        			'message'=>$e->getMessage()
        		);
        	} else {
            	$this->_getSession()->addError(Mage::helper('udropship')->__($e->getMessage()));
        	}
        }
        if ($this->getRequest()->getParam('use_json_response')) {
        	$this->getResponse()->setBody(
        		Mage::helper('core')->jsonEncode($result)
        	);
        } else {
        	$this->_redirect('udropship/vendor/', array('_current'=>true, '_query'=>array('submit_action'=>'')));
        }
    }

    public function existingLabelBatchAction()
    {
    	$result = array();
        try {
            $shipments = $this->getVendorShipmentCollection();
            if (!$shipments->getSize()) {
                Mage::throwException(Mage::helper('udropship')->__('No shipments found for these criteria'));
            }

            Mage::getModel('udropship/label_batch')
                ->setVendor(Mage::getSingleton('udropship/session')->getVendor())
                ->renderShipments($shipments)
                ->prepareLabelsDownloadResponse();

        } catch (Exception $e) {
        	if ($this->getRequest()->getParam('use_json_response')) {
        		$result = array(
        			'error'=>true,
        			'message'=>$e->getMessage()
        		);
        	} else {
            	$this->_getSession()->addError(Mage::helper('udropship')->__($e->getMessage()));
        	}
        }
    	if ($this->getRequest()->getParam('use_json_response')) {
        	$this->getResponse()->setBody(
        		Mage::helper('core')->jsonEncode($result)
        	);
        } else {
        	$this->_redirect('udropship/vendor/', array('_current'=>true, '_query'=>array('submit_action'=>'')));
        }
    }

    public function updateShipmentsStatusAction()
    {
        $hlp = Mage::helper('udropship');
        try {
            $shipments = $this->getVendorShipmentCollection();
            $status = $this->getRequest()->getParam('update_status');

            $statusShipped = Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_SHIPPED;
            $statusDelivered = Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_DELIVERED;

            if (!$shipments->getSize()) {
                Mage::throwException(Mage::helper('udropship')->__('No shipments found for these criteria'));
            }
            if (is_null($status) || $status==='') {
                Mage::throwException(Mage::helper('udropship')->__('No status selected'));
            }

            $shipmentStatuses = false;
            if (Mage::getStoreConfig('udropship/vendor/is_restrict_shipment_status')) {
                $shipmentStatuses = Mage::getStoreConfig('udropship/vendor/restrict_shipment_status');
                if (!is_array($shipmentStatuses)) {
                    $shipmentStatuses = explode(',', $shipmentStatuses);
                }
            }
            foreach ($shipments as $shipment) {
                if (!$shipmentStatuses || (in_array($shipment->getUdropshipStatus(), $shipmentStatuses) && in_array($status, $shipmentStatuses))) {
                    if ($status==$statusShipped || $status==$statusDelivered) {
                        $tracks = $shipment->getAllTracks();
                        if (count($tracks)) {
                            foreach ($tracks as $track) {
                                $hlp->processTrackStatus($track, true, true);
                            }
                        } else {
                            $hlp->completeShipment($shipment, true, $status==$statusDelivered);
                            $hlp->completeOrderIfShipped($shipment, true);
                            $hlp->completeUdpoIfShipped($shipment, true);
                        }
                    }
                    $shipment->setUdropshipStatus($status)->save();
                }
            }
            $this->_getSession()->addSuccess(Mage::helper('udropship')->__('Shipment status has been updated for the selected shipments'));
        } catch (Exception $e) {
            $this->_getSession()->addError(Mage::helper('udropship')->__($e->getMessage()));
        }
        $this->_redirect('udropship/vendor/', array('_current'=>true, '_query'=>array('submit_action'=>'')));
    }

    public function reprintLabelBatchAction()
    {
        $hlp = Mage::helper('udropship');

        if (($trackId = $this->getRequest()->getParam('track_id'))) {
            $track = Mage::getModel('sales/order_shipment_track')->load($trackId);
            if (!$track->getId()) {
                return;
            }
            $labelModel = $hlp->getLabelTypeInstance($track->getLabelFormat());
            $labelModel->printTrack($track);
        }

        if (($batchId = $this->getRequest()->getParam('batch_id'))) {
            $batch = Mage::getModel('udropship/label_batch')->load($batchId);
            if (!$batch->getId()) {
                return;
            }
            $labelModel = Mage::helper('udropship')->getLabelTypeInstance($batch->getLabelType());
            $labelModel->printBatch($batch);
        }
    }

    protected function _preparePackingSlips($shipments)
    {
        $vendorId = $this->_getSession()->getId();
        $vendor = Mage::helper('udropship')->getVendor($vendorId);

        foreach ($shipments as $shipment) {
            if ($shipment->getUdropshipVendor()!=$vendorId) {
                Mage::throwException(Mage::helper('udropship')->__('You are not authorized to print this shipment'));
            }
        }

        if (Mage::getStoreConfig('udropship/vendor/ready_on_packingslip')) {
            foreach ($shipments as $shipment) {
                Mage::helper('udropship')->addShipmentComment(
                    $shipment,
                    Mage::helper('udropship')->__('%s printed packing slip', $vendor->getVendorName())
                );
                if ($shipment->getUdropshipStatus()==Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_PENDING) {
                    $shipment->setUdropshipStatus(Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_READY);
                }
                $shipment->save();
            }
        }

        foreach ($shipments as $shipment) {
            $order = $shipment->getOrder();
            $order->setData('__orig_shipping_amount', $order->getShippingAmount());
            $order->setData('__orig_base_shipping_amount', $order->getBaseShippingAmount());
            $order->setShippingAmount($shipment->getShippingAmount());
            $order->setBaseShippingAmount($shipment->getBaseShippingAmount());
        }

        $theme = explode('/', Mage::getStoreConfig('udropship/admin/interface_theme', 0));
        Mage::getDesign()->setArea('adminhtml')
            ->setPackageName(!empty($theme[0]) ? $theme[0] : 'default')
            ->setTheme(!empty($theme[1]) ? $theme[1] : 'default');

        $pdf = Mage::helper('udropship')->getVendorShipmentsPdf($shipments);
        $filename = 'packingslip'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf';

        foreach ($shipments as $shipment) {
            $order = $shipment->getOrder();
            $order->setShippingAmount($order->getData('__orig_shipping_amount'));
            $order->setBaseShippingAmount($order->getData('__orig_base_shipping_amount'));
        }

        Mage::helper('udropship')->sendDownload($filename, $pdf->render(), 'application/x-pdf');
    }

    public function wysiwygAction()
    {
        $elementId = $this->getRequest()->getParam('element_id', md5(microtime()));
        $storeId = $this->getRequest()->getParam('store_id', 0);
        $storeMediaUrl = Mage::app()->getStore($storeId)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);

        $content = $this->getLayout()->createBlock('udropship/vendor_wysiwyg_form_content', '', array(
            'editor_element_id' => $elementId,
            'store_id'          => $storeId,
            'store_media_url'   => $storeMediaUrl,
        ));

        $this->getResponse()->setBody($content->toHtml());
    }

    public function getVendorShipmentCollection()
    {
        return Mage::helper('udropship')->getVendorShipmentCollection();
    }
}
