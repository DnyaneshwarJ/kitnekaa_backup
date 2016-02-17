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

abstract class Unirgy_Dropship_Model_Label_Abstract_Type extends Varien_Object
{
    /**
    * Send batch file PDF download
    *
    */
    public function printBatch($batch=null)
    {
        $data = $this->renderBatchContent($batch);
        Mage::helper('udropship')->sendDownload($data['filename'], $data['content'], $data['type']);
    }

    /**
    * Send PDF download only for 1 track
    *
    * @param Mage_Sales_Model_Order_Shipment_Track $track
    */
    public function printTrack($track=null)
    {
        $data = $this->renderTrackContent($track);
        Mage::helper('udropship')->sendDownload($data['filename'], $data['content'], $data['type']);
    }

    public function getBatchPathName($batch)
    {
        return Mage::getConfig()->getVarDir('batch').DS.$this->getBatchFileName($batch);
    }

    protected function _getTrackVendorId($track)
    {
        $vId = null;
        if ($track instanceof Unirgy_Rma_Model_Rma_Track) {
            $vId = $track->getRma()->getUdropshipVendor();
        } elseif ($track instanceof Mage_Sales_Model_Order_Shipment_Track) {
            $vId = $track->getShipment()->getUdropshipVendor();
        }
        return $vId;
    }

    protected function _getTrackVendor($track)
    {
        return Mage::helper('udropship')->getVendor($this->_getTrackVendorId($track));
    }
}