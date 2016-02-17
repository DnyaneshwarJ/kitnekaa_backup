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

class Unirgy_Dropship_Block_Adminhtml_Order_Shipments
    extends Mage_Adminhtml_Block_Sales_Order_View_Tab_Shipments
{

    protected function _prepareCollection()
    {
        //TODO: add full name logic
        $collection = Mage::getResourceModel('sales/order_shipment_collection')
            ->addAttributeToSelect('entity_id')
            ->addAttributeToSelect('increment_id')
            ->addAttributeToSelect('created_at')
            ->addAttributeToSelect('total_qty')
            ->addAttributeToSelect('udropship_status')
            ->addAttributeToSelect('udropship_vendor')
            ->addAttributeToSelect('udropship_method_description')
            ->addAttributeToSelect('base_shipping_amount')
            ->setOrderFilter($this->getOrder())
        ;

        $this->setCollection($collection);
        return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('increment_id', array(
            'header' => Mage::helper('udropship')->__('Shipment #'),
            'index' => 'increment_id',
        ));

        $this->addColumn('created_at', array(
            'header' => Mage::helper('udropship')->__('Date Shipped'),
            'index' => 'created_at',
            'type' => 'datetime',
        ));

        $this->addColumn('total_qty', array(
            'header' => Mage::helper('udropship')->__('Total Qty'),
            'index' => 'total_qty',
            'type'  => 'number',
        ));

        $this->addColumn('base_shipping_amount', array(
            'header' => Mage::helper('udropship')->__('Shipping Price'),
            'index' => 'base_shipping_amount',
            'type'  => 'price',
            'currency_code' => $this->getOrder()->getBaseCurrencyCode(),
        ));

        $this->addColumn('udropship_status', array(
            'header' => Mage::helper('udropship')->__('Status'),
            'index' => 'udropship_status',
            'type' => 'options',
            'options' => Mage::getSingleton('udropship/source')->setPath('shipment_statuses')->toOptionHash(),
        ));

        $this->addColumn('udropship_vendor', array(
            'header' => Mage::helper('udropship')->__('Vendor'),
            'index' => 'udropship_vendor',
            'type' => 'options',
            'options' => Mage::getSingleton('udropship/source')->setPath('vendors')->toOptionHash(),
            'filter' => 'udropship/vendor_gridColumnFilter'
        ));

        $this->addColumn('udropship_method_description', array(
            'header' => Mage::helper('udropship')->__('Method'),
            'index' => 'udropship_method_description',
        ));
        return Mage_Adminhtml_Block_Widget_Grid::_prepareColumns();
    }
}
