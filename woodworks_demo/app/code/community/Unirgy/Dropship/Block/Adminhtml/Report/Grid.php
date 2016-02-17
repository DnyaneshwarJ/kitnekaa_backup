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

class Unirgy_Dropship_Block_Adminhtml_Report_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('reportGrid');
        $this->setDefaultSort('order_created_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    public function t($table)
    {
        return Mage::getSingleton('core/resource')->getTableName($table);
    }

    protected $_couponCodeColumn;
    
    protected function _getFlatExpressionColumn($key, $bypass=true)
    {
    	$result = $bypass ? $key : null;
    	switch ($key) {
            case 'tracking_price':
    			$result = new Zend_Db_Expr("(select sum(IFNULL(st.final_price,0)) from {$this->t('sales_flat_shipment_track')} st where parent_id=main_table.entity_id)");
    			break;
    		case 'tracking_ids':
    			$result = new Zend_Db_Expr("(select group_concat(concat(st.".Mage::helper('udropship')->trackNumberField().", ' (', IFNULL(round(st.final_price,2),'N/A'), ')') separator '\n') from {$this->t('sales_flat_shipment_track')} st where parent_id=main_table.entity_id)");
    			break;
    		case 'base_tax_amount':
    			$result = new Zend_Db_Expr("(select sum(oi.base_tax_amount) from {$this->t('sales_flat_order_item')} oi inner join {$this->t('sales_flat_shipment_item')} si where si.order_item_id=oi.item_id and si.parent_id=main_table.entity_id and oi.order_id=main_table.order_id)");
    			break;
    		case 'coupon_codes':
		    	if (Mage::helper('udropship')->isModuleActive('Unirgy_Giftcert')) {
					$result = new Zend_Db_Expr("concat(
						IF(o.coupon_code is not null and o.coupon_code!='', concat('Coupon: ',o.coupon_code), ''),
						IF(o.giftcert_code is not null and o.giftcert_code!='', 
							CONCAT(
								IF(o.coupon_code is not null and o.coupon_code!='', '\n', ''),
								concat('Giftcert: ',o.giftcert_code)
							),
							'')
					)");
				} else {
					$result = new Zend_Db_Expr("
						IF(o.coupon_code is not null and o.coupon_code!='', concat('Coupon: ',o.coupon_code), '')
					");
				}
				break;
    	}
    	return $result;
    }
    
    protected function _prepareCollection()
    {
        if (Mage::helper('udropship')->isSalesFlat()) {

            $res = Mage::getSingleton('core/resource');

            $collection = Mage::getResourceModel('sales/order_shipment_grid_collection');
            $collection->getSelect()
                ->join(array('t'=>$res->getTableName('sales/shipment')), 't.entity_id=main_table.entity_id', array('udropship_vendor', 'udropship_available_at', 'udropship_method', 'udropship_method_description', 'udropship_status', 'base_shipping_amount', 'base_subtotal'=>'base_total_value', 'total_cost'))
                ->join(array('o'=>$res->getTableName('sales/order')), 'o.entity_id=main_table.order_id', array('base_grand_total', 'order_status'=>'o.status'))
                ->join(array('a'=>$res->getTableName('sales/order_address')), 'a.parent_id=o.entity_id and a.address_type="shipping"', array('region_id'))
                ->columns(array(
                    'tracking_price'=>$this->_getFlatExpressionColumn('tracking_price'),
                    'tracking_ids'=>$this->_getFlatExpressionColumn('tracking_ids'),
                    //'subtotal'=>$subtotal,
                    'base_tax_amount'=>$this->_getFlatExpressionColumn('base_tax_amount'),
                	'coupon_codes' => $this->_getFlatExpressionColumn('coupon_codes')
                ));
        } else {

            $eav = Mage::getSingleton('eav/config');
            $sioAttr = $eav->getAttribute('shipment_item', 'order_item_id');
            $stnAttr = $eav->getAttribute('shipment_track', 'number');
            $stfpAttr = $eav->getAttribute('shipment_track', 'final_price');
            $oarAttr = $eav->getAttribute('order_address', 'region_id');
            $oatAttr = $eav->getAttribute('order_address', 'address_type');

            $subtotal = "(select sum(row_total) from {$this->t('sales_flat_order_item')} oi inner join {$this->t('sales_order_entity_int')} sio on sio.value=oi.item_id and sio.attribute_id={$sioAttr->getId()} inner join {$this->t('sales_order_entity')} si on si.entity_id=sio.entity_id where si.parent_id=e.entity_id)";

            $taxAmount = "(select sum(oi.base_tax_amount) from {$this->t('sales_flat_order_item')} oi inner join {$this->t('sales_order_entity_int')} sio on sio.value=oi.item_id and sio.attribute_id={$sioAttr->getId()} inner join {$this->t('sales_order_entity')} si on si.entity_id=sio.entity_id where si.parent_id=e.entity_id)";

            $trackingIds = "(select group_concat(concat(stn.value, ' (', IFNULL(round(_stfp.value,2),'N/A'), ')') separator '\n') from {$this->t('sales_order_entity_text')} stn inner join {$this->t('sales_order_entity')} st on st.entity_id=stn.entity_id left join {$this->t('sales_order_entity_decimal')} _stfp on st.entity_id=_stfp.entity_id where stn.attribute_id={$stnAttr->getId()} and _stfp.attribute_id={$stfpAttr->getId()} and st.parent_id=e.entity_id)";

            $trackingPrice = "(select sum(IFNULL(stfp.value,0)) from {$this->t('sales_order_entity_decimal')} stfp inner join {$this->t('sales_order_entity')} st on st.entity_id=stfp.entity_id where stfp.attribute_id={$stfpAttr->getId()} and st.parent_id=e.entity_id)";

            $collection = Mage::getResourceModel('sales/order_shipment_collection')
                ->addAttributeToSelect('increment_id')
                ->addAttributeToSelect('udropship_status')
                ->addAttributeToSelect('udropship_vendor')
                ->addAttributeToSelect('base_shipping_amount')
                ->addAttributeToSelect('total_cost')
                ->addAttributeToSelect('total_qty')
                ->joinAttribute('order_increment_id', 'order/increment_id', 'order_id')
                ->joinAttribute('order_created_at', 'order/created_at', 'order_id')
                ->joinAttribute('order_status', 'order/status', 'order_id')
                ->joinAttribute('order_coupon_code', 'order/coupon_code', 'order_id', null, 'left')
                ->joinAttribute('base_grand_total', 'order/base_grand_total', 'order_id')
                ->joinAttribute('base_currency_code', 'order/base_currency_code', 'order_id', null, 'left')
                ->addExpressionAttributeToSelect('tracking_ids', $trackingIds, 'entity_id')
                ->addExpressionAttributeToSelect('tracking_price', $trackingPrice, 'entity_id')
                ->addExpressionAttributeToSelect('base_subtotal', $subtotal, 'entity_id')
                ->addExpressionAttributeToSelect('base_tax_amount', $taxAmount, 'entity_id')
            ;
            
            if (Mage::helper('udropship')->isModuleActive('Unirgy_Giftcert')) {
            	$collection->joinAttribute('order_giftcert_code', 'order/giftcert_code', 'order_id', null, 'left');
            }
            
            if (Mage::helper('udropship')->isModuleActive('Unirgy_Giftcert')) {
				$couponCodesExpr = "concat(
					IF({{order_coupon_code}} is not null and {{order_coupon_code}}!='', concat('Coupon: ',{{order_coupon_code}}), ''),
					IF({{order_giftcert_code}} is not null and {{order_giftcert_code}}!='', 
						CONCAT(
							IF({{order_coupon_code}} is not null and {{order_coupon_code}}!='', '\n', ''),
							concat('Giftcert: ',{{order_giftcert_code}})
						),
						'')
				)";
                $couponCodesExprAttrs = array('order_giftcert_code');
                if ($collection->getAttribute('order_coupon_code')->getBackend()->isStatic()) {
                    $couponCodesExpr = str_replace('{{order_coupon_code}}', '_table_order_coupon_code.coupon_code', $couponCodesExpr);
                } else {
                    $couponCodesExprAttrs[] = 'order_coupon_code';
                }
			} else {
				$couponCodesExpr = "
					IF({{order_coupon_code}} is not null and {{order_coupon_code}}!='', concat('Coupon: ',{{order_coupon_code}}), '')
				";
                $couponCodesExprAttrs = array();
			}
            if ($collection->getAttribute('order_coupon_code')->getBackend()->isStatic()) {
                $couponCodesExpr = str_replace('{{order_coupon_code}}', '_table_order_coupon_code.coupon_code', $couponCodesExpr);
            } else {
                $couponCodesExprAttrs[] = 'order_coupon_code';
            }
            $collection->addExpressionAttributeToSelect('coupon_codes', new Zend_Db_Expr($couponCodesExpr), $couponCodesExprAttrs);

            $collection->getSelect()
                ->join(array('oa'=>$this->t('sales_order_entity')), 'oa.parent_id=_table_order_increment_id.entity_id', array())
                ->join(array('oat'=>$this->t('sales_order_entity_varchar')), "oat.entity_id=oa.entity_id and oat.attribute_id=".$oatAttr->getId()." and oat.value='shipping'", array())
                ->joinLeft(array('oar'=>$this->t('sales_order_entity_int')), 'oar.entity_id=oa.entity_id and oar.attribute_id='.$oarAttr->getId(), array('region_id'=>'value'))
            ;
        }

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $flat = Mage::helper('udropship')->isSalesFlat();

        $poStr = Mage::helper('udropship')->isUdpoActive() ? 'Shipment' : 'PO';
        
        $hlp = Mage::helper('udropship');
        
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
        
        $this->addColumn('order_status', array(
            'header'    => Mage::helper('udropship')->__('Order Status'),
            'index'     => 'order_status',
            'filter_index' => !$flat ? null : 'o.status',
            'type' => 'options',
            'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
        ));
        
        $this->addColumn('base_grand_total', array(
            'header' => Mage::helper('udropship')->__('Order Total'),
            'index' => 'base_grand_total',
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base'),
        ));
        
        $this->addColumn('increment_id', array(
            'header'    => Mage::helper('udropship')->__("$poStr #"),
            'index'     => 'increment_id',
            'filter_index' => !$flat ? null : 'main_table.increment_id',
            'type'      => 'text',
        ));

        $this->addColumn('created_at', array(
            'header'    => Mage::helper('udropship')->__("$poStr Date"),
            'index'     => 'created_at',
            'filter_index' => !$flat ? null : 'main_table.created_at',
            'type'      => 'datetime',
        ));
        
        $this->addColumn('udropship_status', array(
            'header' => Mage::helper('udropship')->__("$poStr Status"),
            'index' => 'udropship_status',
            'filter_index' => !$flat ? null : 't.udropship_status',
            'type' => 'options',
            'options' => Mage::getSingleton('udropship/source')->setPath('shipment_statuses')->toOptionHash(),
        ));

        $this->addColumn('base_subtotal', array(
            'header' => Mage::helper('udropship')->__("$poStr Subtotal"),
            'index' => 'base_subtotal',
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base'),
        ));
        
        $this->addColumn('total_cost', array(
            'header' => Mage::helper('udropship')->__("$poStr Total Cost"),
            'index' => 'total_cost',
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base'),
        ));

        $this->addColumn('base_tax_amount', array(
            'header' => Mage::helper('udropship')->__("$poStr Tax Amount"),
            'index' => 'base_tax_amount',
        	'filter_index' => !$flat ? null : $this->_getFlatExpressionColumn('base_tax_amount'),
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base'),
        ));
        
        $this->addColumn('base_shipping_amount', array(
            'header' => Mage::helper('udropship')->__("$poStr Shipping Price"),
            'index' => 'base_shipping_amount',
            'filter_index' => !$flat ? null : 't.base_shipping_amount',
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base'),
        ));
        
        $this->addColumn('total_qty', array(
            'header'    => Mage::helper('udropship')->__("$poStr Total Qty"),
            'index'     => 'total_qty',
        	'filter_index' => !$flat ? null : 't.total_qty',
            'type'      => 'number',
        ));
        
        $this->addColumn('udropship_vendor', array(
            'header' => Mage::helper('udropship')->__('Vendor'),
            'index' => 'udropship_vendor',
            'type' => 'options',
            'options' => Mage::getSingleton('udropship/source')->setPath('vendors')->toOptionHash(),
            'filter' => 'udropship/vendor_gridColumnFilter'
        ));
        
        $this->addColumn('tracking_ids', array(
            'header' => Mage::helper('udropship')->__('Tracking #'),
            'index' => 'tracking_ids',
        	'filter_index' => !$flat ? null : $this->_getFlatExpressionColumn('tracking_ids'),
        ));

        $this->addColumn('tracking_price', array(
            'header' => Mage::helper('udropship')->__('Tracking Total'),
            'index' => 'tracking_price',
        	'filter_index' => !$flat ? null : $this->_getFlatExpressionColumn('tracking_price'),
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base'),
        ));

        $this->addColumn('region_id', array(
            'header' => Mage::helper('udropship')->__('Tax State'),
            'index' => 'region_id',
            'type' => 'options',
            'options' => Mage::getSingleton('udropship/source')->getTaxRegions(),
            'filter'    => false,
            'sortable'  => false,
        ));
        
        $this->addColumn('coupon_codes', array(
            'header' => Mage::helper('udropship')->__('Order coupon codes'),
            'index' => 'coupon_codes',
        	'filter_index' => !$flat ? null : $this->_getFlatExpressionColumn('coupon_codes'),
        	'type' => 'text',
        	'nl2br' => true,
        ));

        $this->addExportType('*/*/exportCsv', Mage::helper('udropship')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('udropship')->__('XML'));
        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }
}
