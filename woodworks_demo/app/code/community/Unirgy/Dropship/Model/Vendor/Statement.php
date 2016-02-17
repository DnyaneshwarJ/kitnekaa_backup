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

class Unirgy_Dropship_Model_Vendor_Statement extends Unirgy_Dropship_Model_Vendor_Statement_Abstract
{
    protected $_eventPrefix = 'udropship_vendor_statement';
    protected $_eventObject = 'statement';

    protected function _construct()
    {
        $this->_init('udropship/vendor_statement');
        parent::_construct();
    }

    protected function _getPoCollection()
    {
        $stPoStatuses = $this->getVendor()->getStatementPoStatus();
        if (!is_array($stPoStatuses)) {
            $stPoStatuses = explode(',', $stPoStatuses);
        }
        $poType = $this->getVendor()->getStatementPoType();
        $this->getResource()->fixStatementDate($this->getVendor(), $poType, $stPoStatuses, $this->getOrderDateFrom(), $this->getOrderDateTo());
        if (Mage::helper('udropship')->isSalesFlat()) {
            $res = Mage::getSingleton('core/resource');
            $pos = $poType == 'po' ? Mage::getResourceModel('udpo/po_grid_collection') : Mage::getResourceModel('sales/order_shipment_grid_collection');
            $pos->getSelect()->join(
                array('t'=>$poType == 'po' ? $res->getTableName('udpo/po') : $res->getTableName('sales/shipment')),
                't.entity_id=main_table.entity_id'/*,
                array('udropship_vendor', 'udropship_available_at', 'udropship_method',
                    'udropship_method_description', 'udropship_status', 'shipping_amount'
                )*/
            )
            ->where("t.udropship_status in (?)", $stPoStatuses)
            ->where("t.udropship_vendor=?", $this->getVendorId())
            ->where("t.statement_date IS NOT NULL")
            ->where("t.statement_date!='0000-00-00 00:00:00'")
            ->where("t.statement_date>=?", $this->getOrderDateFrom())
            ->where("t.statement_date<=?", $this->getOrderDateTo())
            ->where("(main_table.statement_id=? OR main_table.statement_id IS NULL OR main_table.statement_id='')", $this->getStatementId())
            ->order('main_table.entity_id asc');
        } else {
            $pos = Mage::getModel('sales/order_shipment')->getCollection()
                ->addAttributeToSelect('*')
                ->joinAttribute('order_increment_id', 'order/increment_id', 'order_id')
                ->joinAttribute('order_created_at', 'order/created_at', 'order_id')
                ->addAttributeToFilter('udropship_vendor', $this->getVendorId())
                ->addAttributeToFilter('statement_date', array('notnull'=>true))
                ->addAttributeToFilter('statement_date', array('neq'=>'0000-00-00 00:00:00'))
                ->addAttributeToFilter('statement_date', array(
                    'date' => true,
                    'from' => $this->getOrderDateFrom(),
                    'to' => $this->getOrderDateTo(),
                ))
                ->addAttributeToSort('po_id', 'asc')
                ->addAttributeToFilter('udropship_status', array('in'=>$stPoStatuses))
                ->addAttributeToFilter('statement_id', array($this->getStatementId(), array('null'=>true), ''), 'left')
            ;
        }
        return $pos;
    }
    
    protected $_poCollection;
    public function getPoCollection($reload=false)
    {
        if (is_null($this->_poCollection) || $reload) {
            $this->_poCollection = $this->_getPoCollection();
            self::processPos($this->_poCollection, $this->getVendor()->getStatementSubtotalBase());
        }
        return $this->_poCollection;
    }

    protected function _getRefundCollection()
    {
        $stPoStatuses = $this->getVendor()->getStatementPoStatus();
        if (!is_array($stPoStatuses)) {
            $stPoStatuses = explode(',', $stPoStatuses);
        }
        $poType = $this->getVendor()->getStatementPoType();
        $res = Mage::getSingleton('core/resource');
        $refunds = Mage::getResourceModel('sales/order_creditmemo_collection');
        $refunds->addFieldToSelect(array('refund_increment_id'=>'increment_id','refund_id'=>'entity_id','refund_shipping_amount'=>'base_shipping_amount'));
        $refunds->getSelect()
        ->join(
            array('o'=>$res->getTableName('sales/order')),
            'o.entity_id=main_table.order_id',
            array()
        )
        ->join(
            array('tg'=>$poType == 'po' ? $res->getTableName('udpo/po_grid') : $res->getTableName('sales/shipment_grid')),
            'tg.order_id=o.entity_id',
            array('order_increment_id','po_increment_id'=>'increment_id','order_id','po_id'=>'entity_id','order_created_at','po_created_at'=>'created_at')
        )
        ->join(
            array('t'=>$poType == 'po' ? $res->getTableName('udpo/po') : $res->getTableName('sales/shipment')),
            't.entity_id=tg.entity_id',
            array('commission_percent','base_shipping_amount')
        )
        ->columns(array('po_type'=>new Zend_Db_Expr("'$poType'")))
        ->where("t.udropship_status in (?)", $stPoStatuses)
        ->where("t.udropship_vendor=?", $this->getVendorId())
        ->where("main_table.created_at>=?", $this->getOrderDateFrom())
        ->where("main_table.created_at<=?", $this->getOrderDateTo())
        ->order('main_table.entity_id asc');

        return $refunds;
    }

    protected $_refundCollection;
    public function getRefundCollection($reload=false)
    {
        if (is_null($this->_refundCollection) || $reload) {
            $this->_refundCollection = $this->_getRefundCollection();
            $this->processRefunds($this->_refundCollection, $this->getVendor()->getStatementSubtotalBase());
        }
        return $this->_refundCollection;
    }

    public function processRefunds($pos, $subtotalBase)
    {
        $poItemsToLoad = array();
        $subtotalKey = $subtotalBase == 'cost' ? 'total_cost' : 'base_total_value';
        foreach ($pos as $po) {
            $id = $po->getPoId().'-'.$po->getRefundId();
            foreach (array($subtotalKey, 'base_tax_amount', 'base_discount_amount') as $k) {
                $poItemsToLoad[$id][$k] = true;
            }
        }
        if ($poItemsToLoad) {
            if ($pos instanceof Varien_Data_Collection) {
                $samplePo = $pos->getFirstItem();
            } else {
                reset($pos);
                $samplePo = current($pos);
            }
            $refundIds = $poIds = array();
            foreach ($poItemsToLoad as $id=>$_dummy) {
                list($poId, $refundId) = explode('-', $id);
                $poIds[] = $poId;
                $refundIds[] = $refundId;
            }
            $poType = $samplePo->getPoType();
            $baseCost = Mage::helper('udropship')->hasMageFeature('order_item.base_cost');

            $poItems = $poType == 'po' ? Mage::getModel('udpo/po_item')->getCollection() : Mage::getModel('sales/order_shipment_item')->getCollection();
            $fields = array('base_price', 'base_tax_amount', 'base_discount_amount', 'qty_ordered');
            $rFields = array('refund_qty'=>'qty');
            if ($baseCost) $fields[] = 'base_cost';
            $poItems->getSelect()
                ->join(array('i'=>$poItems->getTable('sales/order_item')), 'i.item_id=main_table.order_item_id', $fields)
                ->join(array('o'=>$poItems->getTable('sales/order')), 'i.order_id=o.entity_id', array())
                ->join(array('r'=>$poItems->getTable('sales/creditmemo')), 'r.order_id=o.entity_id', array('refund_id'=>'entity_id'))
                ->join(array('ri'=>$poItems->getTable('sales/creditmemo_item')), 'i.item_id=ri.order_item_id and r.entity_id=ri.parent_id', $rFields)
                ->where('main_table.order_item_id<>0 and main_table.parent_id in (?)', array_keys($poIds))
                ->where('r.entity_id in (?)', array_keys($refundIds))
                ->where('concat(main_table.parent_id,"-",r.entity_id) in (?)', array_keys($poItemsToLoad))
            ;

            $itemTotals = array();
            foreach ($poItems as $item) {
                $id = $item->getId();
                if (empty($itemTotals[$id])) {
                    $itemTotals[$id] = array($subtotalKey=>0, 'base_tax_amount'=>0, 'base_discount_amount'=>0);
                }
                $refundQty = min($item->getQty(),$item->getRefundQty());
                $itemTotals[$id][$subtotalKey] += $subtotalBase == 'cost' ? $item->getBaseCost()*$refundQty : $item->getBasePrice()*$refundQty;
                $iTax = $item->getBaseTaxAmount()/max(1,$item->getQtyOrdered());
                $iTax = $iTax*$refundQty;
                $iDiscount = $item->getBaseDiscountAmount()/max(1,$item->getQtyOrdered());
                $iDiscount = $iDiscount*$refundQty;
                $itemTotals[$id]['base_tax_amount'] += $iTax;
                $itemTotals[$id]['base_discount_amount'] += $iDiscount;
            }
            foreach ($itemTotals as $id=>$total) {
                foreach ($total as $k=>$v) {
                    if (!empty($poItemsToLoad[$id][$k])) {
                        $pos->getItemById($id)->setData($k, $v);
                    }
                }
            }
        }
    }
    
    public function addPayout($payout)
    {
        $this->_payouts[] = $payout->getData();
    }
    
    public function fetchOrders()
    {
        $hlp = Mage::helper('udropship');
        $core = Mage::helper('core');
        $vendor = $this->getVendor();

        $this->setPoType($vendor->getStatementPoType());
        
        $this->_resetOrders();
        $this->_resetRefunds();
        $this->_resetTotals();
        $this->_cleanAdjustments();
        $this->_payouts = array();
        $this->setTotalPaid(0);
        
        $pos = $this->getPoCollection();
        $hlp->collectPoAdjustments($pos, true);
        
        Mage::dispatchEvent('udropship_vendor_statement_pos', array(
            'statement'=>$this,
            'pos'=>$pos
        ));
        
        $totals_amount = $this->_totals_amount;
        
        foreach ($pos as $id=>$po) {

            $order = $this->initOrder($po);

            Mage::dispatchEvent('udropship_vendor_statement_row', array(
                'statement'=>$this,
                'po'=>$po,
                'order'=>&$order
            ));

            $order = $this->calculateOrder($order);
            $totals_amount = $this->accumulateOrder($order, $totals_amount);
            
            $this->_orders[$id] = $order;
        }

        if (Mage::helper('udropship')->isStatementRefundsEnabled()) {

        $refunds = $this->getRefundCollection();

        foreach ($refunds as $id=>$refund) {

            $refundRow = $this->initRefund($refund);

            Mage::dispatchEvent('udropship_vendor_statement_refund_row', array(
                'statement'=>$this,
                'refund'=>$refund,
                'refund_row'=>&$refundRow
            ));

            $refundRow = $this->calculateRefund($refundRow);
            $totals_amount = $this->accumulateRefund($refundRow, $totals_amount);

            $this->_refunds[$id] = $refundRow;
        }

        }

        Mage::dispatchEvent('udropship_vendor_statement_totals', array(
            'statement'=>$this,
        	'totals'=>&$totals_amount,
            'totals_amount'=>&$totals_amount
        ));
        
        $this->_totals_amount = $totals_amount;
        
        Mage::dispatchEvent('udropship_vendor_statement_collect_payouts', array(
            'statement'=>$this,
        ));
        
        $this->_calculateAdjustments();
        $this->finishStatement();
        
        return $this;
    }
    
    public function getAdjustmentPrefix()
    {
        return Mage::helper('udropship')->getAdjustmentPrefix('statement');
    }
    
    public function getPdf()
    {
        return Mage::getModel('udropship/pdf_statement')
            ->before()->addStatement($this)->after()->getPdf();
    }
    
    public function createPayout()
    {
        if (!Mage::helper('udropship')->isUdpayoutActive()) Mage::throwException('Payout module is inactive or not installed');
        if ($this->getTotalDue()<=0) Mage::throwException('Statement "total due" must be positive');
        $payout = Mage::helper('udpayout')->createPayout(
                $this->getVendor(), 
                Unirgy_DropshipPayout_Model_Payout::STATUS_PROCESSING, 
                Unirgy_DropshipPayout_Model_Payout::TYPE_STATEMENT
            )
            ->setPoType($this->getPoType())
            ->addOrders($this->getUnpaidOrders(), false)
            ->setStatementId($this->getStatementId())
            ->finishPayout();
        if (abs($this->getTotalDue()-$payout->getTotalDue())>0.001) {
            $payout->addAdjustment(
                $payout->createAdjustment(
                    $this->getTotalDue()-$payout->getTotalDue(), 
                    Mage::helper('udropship')->__('Internal adjustment to sync payout with statement total due')
                )
                ->setForcedAdjustmentPrefix(Mage::helper('udropship')->getAdjustmentPrefix('statement:payout'))
            );
            $payout->finishPayout();
        }
        return $payout;
    }

    public function pay()
    {
        if (!Mage::helper('udropship')->isUdpayoutActive()) Mage::throwException('Payout module is inactive or not installed');
        if ($this->getTotalDue()<=0) Mage::throwException('Statement "total due" must be positive');
        $payout = $this->createPayout();
        $payout->pay();
        $this->completePayout($payout);
    }
    
    public function completePayout($payout)
    {
        $this->mergePaidAmounts($payout);
        $this->markPosPaid()->save();
        $ptCol = Mage::getResourceModel('udpayout/payout_collection')
            ->addFieldToFilter('statement_id', $this->getStatementId())
            ->addFieldToFilter('payout_status', Unirgy_DropshipPayout_Model_Payout::STATUS_HOLD);
        foreach ($ptCol as $pt) {
            $pt->cancel();
        }
    }
    
    public function getUnpaidOrders()
    {
        return $this->_getFilteredOrders(false);
    }
    
    public function getPaidOrders()
    {
        return $this->_getFilteredOrders(true);
    }
    
    protected function _getFilteredOrders($paid=false)
    {
        $filtered = array();
        $this->initTotals();
        foreach ($this->_orders as $sId => $order) {
            if (!empty($order['paid']) == $paid) $filtered[$sId] = $order;
        }
        return $filtered;
    }
    
    public function markPosPaid()
    {
        $this->getResource()->markPosPaid($this);
        $this->initTotals();
        foreach ($this->_orders as &$order) {
            $order['paid'] = true;
        }
        unset($order);
        $this->_compactTotals();
        return $this;
    }

    public function send()
    {
        $hlp = Mage::helper('udropship');
        $core = Mage::helper('core');
        $vendor = $this->getVendor();
        $data = array();

        $store = Mage::app()->getDefaultStoreView();

        $hlp->setDesignStore($store);

        $data['_ATTACHMENTS'][] = array(
            'content'    => $this->getPdf()->render(),
            'filename'   => $this->getStatementFilename(),
            'type'       => 'application/x-pdf',
        );

        $data += array(
            'statement'  => $this,
            'vendor'     => $vendor,
            'store'      => $store,
            'date_from'  => $hlp->dateInternalToLocale($this->getOrderDateFrom(), null, $this->getUseLocaleTimezone()),
            'date_to'    => $hlp->dateInternalToLocale($this->getOrderDateTo(), null, $this->getUseLocaleTimezone()),
        );

        $template = $vendor->getStatementEmailTemplate();
        if (!$template) {
            $template = Mage::getStoreConfig('udropship/statement/email_template');
        }
        $identity = Mage::getStoreConfig('udropship/statement/email_identity');
        Mage::getModel('udropship/email')->sendTransactional(
            $template,
            $identity,
            $vendor->getBillingEmail(),
            $vendor->getVendorName(),
            $data
        );
        $hlp->setDesignStore();

        if (!$this->getEmailSent()) {
            $this->setEmailSent(1)->save();
        }

        return $this;
    }
}
