<?php

class Unirgy_DropshipTierCommission_Model_Payout extends Unirgy_DropshipPayout_Model_Payout
{
    public function addPo($po)
    {
        $core = Mage::helper('core');
        $hlp = Mage::helper('udropship');
        $ptHlp = Mage::helper('udpayout');
        $vendor = $this->getVendor();

        $this->initTotals();

        $hlp->collectPoAdjustments(array($po));
        Mage::helper('udropship')->addVendorSkus($po);

        $onlySubtotal = false;
        foreach ($po->getAllItems() as $item) {
            if ($item->getOrderItem()->isDummy(true)) continue;
            $order = $this->initPoItem($item, $onlySubtotal);
            $onlySubtotal = true;

            Mage::dispatchEvent('udropship_vendor_payout_item_row', array(
                'payout'  => $this,
                'po'      => $po,
                'po_item' => $item,
                'order'   => &$order
            ));

            $order = $this->calculateOrder($order);
            $this->_totals_amount = $this->accumulateOrder($order, $this->_totals_amount);

            $poId = $po->getId() ? $po->getId() : spl_object_hash($po);
            $this->_orders[$poId.'-'.$item->getId()] = $order;
        }

        return $this;
    }

    protected $_roundingDeltas=array();
    protected function _deltaRound($price, $id)
    {
        if ($price) {
            $delta = isset($this->_roundingDeltas[$id]) ? $this->_roundingDeltas[$id] : 0;
            $price += $delta;
            $this->_roundingDeltas[$id] = $price - round($price,2);
            $price = round($price,2);
        }
        return $price;
    }

    public function initPoItem($poItem, $onlySubtotal)
    {
        $po = $poItem->getPo() ? $poItem->getPo() : $poItem->getShipment();
        $orderItem = $poItem->getOrderItem();
        $hlp = Mage::helper('udropship');
        $order = array(
            'po_id' => $po->getId(),
            'date' => $hlp->getPoOrderCreatedAt($po),
            'id' => $hlp->getPoOrderIncrementId($po),
            'po_com_percent' => $po->getCommissionPercent(),
            'com_percent' => $poItem->getCommissionPercent(),
            'trans_fee' => $poItem->getTransactionFee(),
        	'adjustments' => $onlySubtotal ? array() : $po->getAdjustments(),
            'order_id' => $po->getOrderId(),
            'order_created_at' => $hlp->getPoOrderCreatedAt($po),
            'order_increment_id' => $hlp->getPoOrderIncrementId($po),
            'po_increment_id' => $po->getIncrementId(),
            'po_created_at' => $po->getCreatedAt(),
            'po_statement_date' => $po->getStatementDate(),
            'po_type' => $po instanceof Unirgy_DropshipPo_Model_Po ? 'po' : 'shipment',
            'sku' => $poItem->getSku(),
            'simple_sku' => $poItem->getOrderItem()->getProductOptionByCode('simple_sku'),
            'vendor_sku' => $poItem->getVendorSku(),
            'vendor_simple_sku' => $poItem->getVendorSimpleSku(),
            'product' => $poItem->getName(),
            'po_item_id' => $poItem->getId()
        );
        if ($this->getVendor()->getStatementSubtotalBase() == 'cost') {
            if (abs($poItem->getBaseCost())>0.001) {
                $subtotal = $poItem->getBaseCost()*$poItem->getQty();
            } else {
                $subtotal = $orderItem->getBaseCost()*$poItem->getQty();
            }
        } else {
            $subtotal = $orderItem->getBasePrice()*$poItem->getQty();
        }

        $qtyOrdered = $orderItem->getQtyOrdered();
        $_rowDivider = $poItem->getQty()/($qtyOrdered>0 ? $qtyOrdered : 1);
        $iHiddenTax = $orderItem->getBaseHiddenTaxAmount()*($_rowDivider>0 ? $_rowDivider : 1);
        $iTax = $orderItem->getBaseTaxAmount()*($_rowDivider>0 ? $_rowDivider : 1);
        $iDiscount = $orderItem->getBaseDiscountAmount()*($_rowDivider>0 ? $_rowDivider : 1);

        if ($orderItem->getOrder()->getData('udpo_amount_fields') && $poItem->getPo()
            || $orderItem->getOrder()->getData('ud_amount_fields') && $poItem->getShipment()
        ) {
            $iHiddenTax = $poItem->getBaseHiddenTaxAmount();
            $iTax = $poItem->getBaseTaxAmount();
            $iDiscount = $poItem->getBaseDiscountAmount();
            $subtotal = $poItem->getBaseRowTotal();
        }

        $shippingAmount = $po->getBaseShippingAmount();
        if ($this->getVendor()->getIsShippingTaxInShipping()) {
            $shippingAmount += $po->getBaseShippingTax();
        } else {
            if ($onlySubtotal) {
                $iTax += $po->getBaseShippingTax();
            }
        }
        $iTax = $this->_deltaRound($iTax, $po->getId());
        $amountRow = array(
            'subtotal' => $subtotal,
            'shipping' => $onlySubtotal ? 0 : $shippingAmount,
            'tax' => $iTax,
            'hidden_tax' => $iHiddenTax,
            'discount' => $iDiscount,
            'handling' => $onlySubtotal ? 0 : $po->getBaseHandlingFee(),
            'trans_fee' => $onlySubtotal ? 0 : $po->getTransactionFee(),
            'adj_amount' => $onlySubtotal ? 0 : $po->getAdjustmentAmount(),
        );
        foreach ($amountRow as &$_ar) {
            $_ar = is_null($_ar) ? 0 : $_ar;
        }
        unset($_ar);
        $order['amounts'] = array_merge($this->_getEmptyTotals(), $amountRow);
        return $order;
    }

    public function calculateOrder($order)
    {
        $taxInSubtotal = Mage::helper('tax')->displaySalesBothPrices() || Mage::helper('tax')->displaySalesPriceInclTax();
        if (is_null($order['com_percent'])) {
            $order['com_percent'] = $this->getVendor()->getCommissionPercent();
        }
        $order['com_percent'] *= 1;
        if (is_null($order['po_com_percent'])) {
            $order['po_com_percent'] = $this->getVendor()->getCommissionPercent();
        }
        $order['po_com_percent'] *= 1;

        if (isset($order['amounts']['tax']) && in_array($this->getVendor()->getStatementTaxInPayout(), array('', 'include'))) {
            if ($taxInSubtotal) {
                if ($this->getVendor()->getApplyCommissionOnTax()) {
                    $order['amounts']['subtotal'] += $order['amounts']['tax'];
                    $order['amounts']['subtotal'] += $order['amounts']['hidden_tax'];
                    $order['amounts']['com_amount'] = $order['amounts']['subtotal']*$order['com_percent']/100;
                } else {
                    $order['amounts']['com_amount'] = $order['amounts']['subtotal']*$order['com_percent']/100;
                    $order['amounts']['subtotal'] += $order['amounts']['tax'];
                    $order['amounts']['subtotal'] += $order['amounts']['hidden_tax'];
                }
            } else {
                $order['amounts']['com_amount'] = $order['amounts']['subtotal']*$order['com_percent']/100;
                $order['amounts']['total_payout']  += $order['amounts']['tax'];
                $order['amounts']['total_payout']  += $order['amounts']['hidden_tax'];
                $order['amounts']['total_payment'] += $order['amounts']['tax'];
                $order['amounts']['total_payment'] += $order['amounts']['hidden_tax'];
                if ($this->getVendor()->getApplyCommissionOnTax()) {
                    $taxCom = round($order['amounts']['tax']*$order['com_percent']/100, 2);
                    $order['amounts']['com_amount'] += $taxCom;
                    $order['amounts']['total_payout'] -= $taxCom;
                }
            }
        } else {
            $order['amounts']['com_amount'] = $order['amounts']['subtotal']*$order['com_percent']/100;
        }

        $order['amounts']['com_amount'] = round($order['amounts']['com_amount'], 2);

        $order['amounts']['total_payout'] = $order['amounts']['subtotal']-$order['amounts']['com_amount']-$order['amounts']['trans_fee']+$order['amounts']['adj_amount'];
        $order['amounts']['total_payment'] = $order['amounts']['subtotal']+$order['amounts']['adj_amount'];

        if (isset($order['amounts']['discount']) && in_array($this->getVendor()->getStatementDiscountInPayout(), array('', 'include'))) {
            if ($this->getVendor()->getApplyCommissionOnDiscount()) {
                $discountCom = round($order['amounts']['discount']*$order['com_percent']/100, 2);
                $order['amounts']['com_amount'] -= $discountCom;
                $order['amounts']['total_payout'] += $discountCom;
            }
            $order['amounts']['total_payout'] -= $order['amounts']['discount'];
        }
        $order['amounts']['total_payment'] -= $order['amounts']['discount'];

        if (isset($order['amounts']['shipping']) && in_array($this->getVendor()->getStatementShippingInPayout(), array('', 'include'))) {
            if ($this->getVendor()->getApplyCommissionOnShipping()) {
                $shipCom = round($order['amounts']['shipping']*$order['po_com_percent']/100, 2);
                $order['amounts']['com_amount'] += $shipCom;
                $order['amounts']['total_payout'] -= $shipCom;
            }
            $order['amounts']['total_payout'] += $order['amounts']['shipping'];
        }
        $order['amounts']['total_payment'] += $order['amounts']['shipping'];
        $order['amounts']['total_invoice'] = $order['amounts']['com_amount']+$order['amounts']['trans_fee']+$order['amounts']['adj_amount'];

        return $order;
    }

    protected function _compactTotals()
    {
        parent::_compactTotals();
        $ordersCnt = array();
        foreach ($this->getOrders() as $order) {
            $ordersCnt[$order['po_id']] = 1;
        }
        $this->setTotalOrders(array_sum($ordersCnt));
        return $this;
    }
}