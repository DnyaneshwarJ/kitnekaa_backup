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

class Unirgy_Dropship_Block_Adminhtml_Shipment_Grid
    extends Mage_Adminhtml_Block_Sales_Shipment_Grid
{
    protected function _prepareCollection()
    {
        if (Mage::helper('udropship')->isSalesFlat()) {
            $res = Mage::getSingleton('core/resource');
            $collection = Mage::getResourceModel('sales/order_shipment_grid_collection');
            $collection->getSelect()->join(
                array('t'=>$res->getTableName('sales/shipment')), 
                't.entity_id=main_table.entity_id', 
                array('udropship_vendor', 'udropship_available_at', 'udropship_method', 
                    'udropship_method_description', 'udropship_status', 'shipping_amount'
                )
            );
            $collection->setFlag('ee_gws_store_use_main', 1);
            /*
            $refCol = new ReflectionObject($collection);
            $refPro = $refCol->getProperty('_map');
            if ($refPro) {
                $oldAccess = $refPro->isProtected() || $refPro->isPrivate();
                $refPro->setAccessible(true);
                $map = $refPro->getValue($collection);
                $map['fields']['store_id'] = 'main_table.store_id';
                $refPro->setValue($collection, $map);
                $refPro->setAccessible($oldAccess);
            }
            */
        } else {
            $collection = Mage::getResourceModel('sales/order_shipment_collection')
                ->addAttributeToSelect('increment_id')
                ->addAttributeToSelect('created_at')
                ->addAttributeToSelect('total_qty')
                ->addAttributeToSelect('udropship_status')
                ->addAttributeToSelect('udropship_vendor')
                ->addAttributeToSelect('udropship_method_description')
                ->addAttributeToSelect('shipping_amount')
                ->joinAttribute('shipping_firstname', 'order_address/firstname', 'shipping_address_id', null, 'left')
                ->joinAttribute('shipping_lastname', 'order_address/lastname', 'shipping_address_id', null, 'left')
                ->joinAttribute('order_increment_id', 'order/increment_id', 'order_id', null, 'left')
                ->joinAttribute('order_created_at', 'order/created_at', 'order_id', null, 'left')
                ->joinAttribute('base_currency_code', 'order/base_currency_code', 'order_id', null, 'left')
            ;
        }
        $this->setCollection($collection);
        return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $flat = Mage::helper('udropship')->isSalesFlat();
        
        $this->addColumn('increment_id', array(
            'header'    => Mage::helper('udropship')->__('Shipment #'),
            'index'     => 'increment_id',
            'filter_index' => !$flat ? null : 'main_table.increment_id',
            'type'      => 'text',
        ));

        $this->addColumn('created_at', array(
            'header'    => Mage::helper('udropship')->__('Date Shipped'),
            'index'     => 'created_at',
            'filter_index' => !$flat ? null : 'main_table.created_at',
            'type'      => 'datetime',
        ));

        $this->addColumn('order_increment_id', array(
            'header'    => Mage::helper('udropship')->__('Order #'),
            'index'     => 'order_increment_id',
            'type'      => 'number',
        ));

        $this->addColumn('order_created_at', array(
            'header'    => Mage::helper('udropship')->__('Order Date'),
            'index'     => 'order_created_at',
            'type'      => 'datetime',
        ));

        if (Mage::helper('udropship')->isSalesFlat()) {
            $this->addColumn('shipping_name', array(
                'header' => Mage::helper('udropship')->__('Ship to Name'),
                'index' => 'shipping_name',
            ));
        } else {
            $this->addColumn('shipping_firstname', array(
                'header' => Mage::helper('udropship')->__('Ship to First name'),
                'index' => 'shipping_firstname',
            ));

            $this->addColumn('shipping_lastname', array(
                'header' => Mage::helper('udropship')->__('Ship to Last name'),
                'index' => 'shipping_lastname',
            ));
        }

        $this->addColumn('total_qty', array(
            'header' => Mage::helper('udropship')->__('Total Qty'),
            'index' => 'total_qty',
            'filter_index' => !$flat ? null : 'main_table.total_qty',
            'type'  => 'number',
        ));

        $this->addColumn('shipping_amount', array(
            'header' => Mage::helper('udropship')->__('Shipping Price'),
            'index' => 'shipping_amount',
            'type'  => 'number',
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base'),
        ));

        $this->addColumn('udropship_status', array(
            'header' => Mage::helper('udropship')->__('Status'),
            'index' => 'udropship_status',
            'type' => 'options',
            'options' => Mage::getSingleton('udropship/source')->setPath('shipment_statuses')->toOptionHash(),
        ));

        $this->addColumn('statement_date', array(
            'header' => Mage::helper('udropship')->__('Statement Ready At'),
            'index' => 'statement_date',
            'type'  => 'date',
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

        $this->addColumn('action',
            array(
                'header'    => Mage::helper('udropship')->__('Action'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'     => 'getId',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('udropship')->__('View'),
                        'url'     => array('base'=>'*/*/view'),
                        'field'   => 'shipment_id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'is_system' => true
        ));

        return Mage_Adminhtml_Block_Widget_Grid::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        parent::_prepareMassaction();

        if (!Mage::helper('udropship')->isUdpoActive()) {
            $this->getMassactionBlock()->addItem('resendPo', array(
                 'label'=> Mage::helper('udropship')->__('Resend PO Notifications'),
                 'url'  => $this->getUrl('adminhtml/udropshipadmin_shipment/resendPo'),
            ));
        }

        Mage::dispatchEvent('udropship_adminhtml_shipment_grid_prepare_massaction', array('grid'=>$this));

        return $this;
    }
}
