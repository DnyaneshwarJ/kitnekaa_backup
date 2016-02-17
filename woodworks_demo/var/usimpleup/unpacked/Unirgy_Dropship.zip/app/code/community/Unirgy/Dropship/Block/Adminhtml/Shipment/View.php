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

class Unirgy_Dropship_Block_Adminhtml_Shipment_View
    extends Mage_Adminhtml_Block_Sales_Order_Shipment_View
{
    public function __construct()
    {
        parent::__construct();

        $shipment = $this->getShipment();
        if (($id = $shipment->getId()) && $shipment->getUdropshipStatus()!=Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_SHIPPED) {
            $url = $this->getUrl('adminhtml/udropshipadmin_shipment/ship', array(
                'id'=>$id,
                'order_id'=>$this->getRequest()->getParam('order_id')
            ));
            $this->_addButton('ship', array(
                'label'     => Mage::helper('udropship')->__('Mark as shipped'),
                'class'     => 'save',
                'onclick'   => "setLocation('$url')"
            ));
        }
    }

    public function getHeaderText()
    {
        $header = parent::getHeaderText();
        $status = $this->getShipment()->getUdropshipStatus();
        if (is_numeric($status)) {
            $statuses = Mage::getSingleton('udropship/source')->setPath('shipment_statuses')->toOptionHash();
            $header .= ' ['.$statuses[$this->getShipment()->getUdropshipStatus()].']';
        }
        return $header;
    }
}