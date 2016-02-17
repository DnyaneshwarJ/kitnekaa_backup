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

class Unirgy_Dropship_Model_Label_Fedex
    extends Mage_Usa_Model_Shipping_Carrier_Fedex
    implements Unirgy_Dropship_Model_Label_Interface_Carrier
{
    protected $_codeUnderscore = array(
        'PRIORITYOVERNIGHT' => 'PRIORITY_OVERNIGHT',
        'STANDARDOVERNIGHT' => 'STANDARD_OVERNIGHT',
        'FIRSTOVERNIGHT' => 'FIRST_OVERNIGHT',
        'FEDEX2DAY' => 'FEDEX_2_DAY',
        'FEDEX2DAYAM' => 'FEDEX_2_DAY_AM',
        'FEDEXEXPRESSSAVER' => 'FEDEX_EXPRESS_SAVER',
        'INTERNATIONALPRIORITY' => 'INTERNATIONAL_PRIORITY',
        'INTERNATIONALECONOMY' => 'INTERNATIONAL_ECONOMY',
        'INTERNATIONALFIRST' => 'INTERNATIONAL_FIRST',
        'FEDEX1DAYFREIGHT' => 'FEDEX_1_DAY_FREIGHT',
        'FEDEX2DAYFREIGHT' => 'FEDEX_2_DAY_FREIGHT',
        'FEDEX3DAYFREIGHT' => 'FEDEX_3_DAY_FREIGHT',
        'FEDEXGROUND' => 'FEDEX_GROUND',
        'GROUNDHOMEDELIVERY' => 'GROUND_HOME_DELIVERY',
        'INTERNATIONALPRIORITY FREIGHT' => 'INTERNATIONAL_PRIORITY_FREIGHT',
        'INTERNATIONALECONOMY FREIGHT' => 'INTERNATIONAL_ECONOMY_FREIGHT',
        'EUROPEFIRSTINTERNATIONALPRIORITY' => 'EUROPE_FIRST_INTERNATIONAL_PRIORITY',
        'FEDEXFREIGHTECONOMY' => 'FEDEX_FREIGHT_ECONOMY',
        'SMARTPOST' => 'SMART_POST',
        'FEDEXFIRSTFREIGHT' => 'FEDEX_FIRST_FREIGHT',
        'FEDEXFREIGHTPRIORITY' => 'FEDEX_FREIGHT_PRIORITY',
    );

    public function requestLabel($track)
    {
        $hlp = Mage::helper('udropship');
        $this->_track = $track;

        $this->_shipment = $this->_track->getShipment();
        $this->_order = $this->_shipment->getOrder();
        $orderId = $this->_order->getIncrementId();

        $poId = $this->_shipment->getIncrementId();
        if ($hlp->isUdpoActive() && ($udpo = Mage::helper('udpo')->getShipmentPo($this->_shipment))) {
            $poId = $udpo->getIncrementId();
        }

        $this->_reference = $this->_track->getReference() ? $this->_track->getReference() : $orderId;

        $this->_address = $this->_order->getShippingAddress();
        $store = $this->_order->getStore();
        $currencyCode = $this->_order->getBaseCurrencyCode();
        $v = $this->getVendor();

        $skus = array();
        foreach ($this->getMpsRequest('items') as $_item) {
            $item = is_array($_item) ? $_item['item'] : $_item;
            $skus[] = $item->getSku();
        }

        $fedexData = array();
        foreach (array(
            'fedex_dropoff_type',
            'fedex_signature_option',
        ) as $fedexKey) {
            $fedexData[$fedexKey] = $track->hasData($fedexKey) ? $track->getData($fedexKey) : $v->getData($fedexKey);
        }
        $fedexData = new Varien_Object($fedexData);

        $weight = $this->_track->getWeight();
        if (!$weight || $this->_shipment->getSkipTrackDataWeight()) {
            $weight = 0;
            $parentItems = array();
            foreach ($this->getMpsRequest('items') as $_item) {
                $item = $_item['item'];

                $_weight = 0;
                $orderItem = $item->getOrderItem();
                if ($orderItem->getParentItem()) {
                    $weightType = $orderItem->getParentItem()->getProductOptionByCode('weight_type');
                    if (null !== $weightType && !$weightType) {
                        $_weight = (!empty($_item['weight']) ? $_item['weight'] : $item->getWeight());
                    }
                } else {
                    $weightType = $orderItem->getProductOptionByCode('weight_type');
                    if (null === $weightType || $weightType) {
                        $_weight = (!empty($_item['weight']) ? $_item['weight'] : $item->getWeight());
                    }
                }

                $children = $orderItem->getChildrenItems() ? $orderItem->getChildrenItems() : $orderItem->getChildren();
                if ($children) {
                    $parentItems[$orderItem->getId()] = $item;
                }
                $__qty = $item->getQty();
                if ($orderItem->isDummy(true)) {
                    if (($_parentItem = $orderItem->getParentItem())) {
                        $__qty = $orderItem->getQtyOrdered()/$_parentItem->getQtyOrdered();
                        if (@$parentItems[$_parentItem->getId()]) {
                            $__qty *= $parentItems[$_parentItem->getId()]->getQty();
                        }
                    } else {
                        $__qty = max(1,$item->getQty());
                    }
                }

                $_qty = (!empty($_item['qty']) ? $_item['qty'] : $__qty);
                $weight += $_weight*$_qty;
            }
        }

        $value = $this->_track->getValue();
        if (!$value) {
            $value = 0;
            foreach ($this->getMpsRequest('items') as $_item) {
                $item = $_item['item'];
                $_qty = (!empty($_item['qty']) ? $_item['qty'] : $item->getQty());
                $value += ($item->getBasePrice() ? $item->getBasePrice() : $item->getPrice())*$_qty;
            }
        }
        $value = round($value, 2);
        $weight = sprintf("%.2f", max($weight, 1/16));
        $totalWeight = $this->_track->getTotalWeight();
        if (!$totalWeight || $this->_shipment->getSkipTrackDataWeight()) {
            $totalWeight = $weight;
        }

        $length = $this->_track->getLength() ? $this->_track->getLength() : $v->getDefaultPkgLength();
        $width = $this->_track->getWidth() ? $this->_track->getWidth() : $v->getDefaultPkgWidth();
        $height = $this->_track->getHeight() ? $this->_track->getHeight() : $v->getDefaultPkgHeight();

        $a = $this->_address;

        if (($shippingMethod = $this->_shipment->getUdropshipMethod())) {
            $arr = explode('_', $shippingMethod, 2);
            $carrierCode = $arr[0];
            $methodCode = $arr[1];
        } else {
            $ship = explode('_', $this->_order->getShippingMethod(), 2);
            $carrierCode = $ship[0];
            $methodCode = $v->getShippingMethodCode($ship[1]);
        }

        $isFedexSoap = $carrierCode=='fedexsoap'||$hlp->hasMageFeature('fedex.soap');

        if ($carrierCode=='fedexsoap') {
            $services = $hlp->getCarrierMethods('fedexsoap');
        } else {
            $services = $hlp->getCarrierMethods('fedex');
        }
#echo "<pre>"; print_r($services); echo "</pre>".$methodCode;
        if (!$isFedexSoap && (empty($services[$methodCode]) || empty($this->_codeUnderscore[$methodCode]))
            || $isFedexSoap && (empty($services[$methodCode]) || false === array_search($methodCode, $this->_codeUnderscore))
        ) {
            Mage::throwException('Invalid shipping method');
        }

        if (!$isFedexSoap) {
            $serviceCode = $this->_codeUnderscore[$methodCode];
        } else {
            $serviceCode = $methodCode;
        }

        $isHomeDelivery = $serviceCode == 'GROUND_HOME_DELIVERY';

        $keyRequestedPackages = $this->getShipServiceVersion('RequestedPackages');

        $isSmartPost = in_array($serviceCode, array('SMART_POST'));

        $shipment = array(
            'ShipTimestamp' => date('c', Mage::helper('udropship')->getNextWorkDayTime()),
            'DropoffType' => $fedexData->getFedexDropoffType(),//'REGULAR_PICKUP', // REGULAR_PICKUP, REQUEST_COURIER, DROP_BOX, BUSINESS_SERVICE_CENTER and STATION
            'ServiceType' => $serviceCode,//'PRIORITY_OVERNIGHT', // STANDARD_OVERNIGHT, PRIORITY_OVERNIGHT, FEDEX_GROUND, ...
            // Express US: PRIORITY_OVERNIGHT, STANDARD_OVERNIGHT, FEDEX_2_DAY, FEDEX_EXPRESS_SAVER, FIRST_OVERNIGHT
            'PackagingType' => 'YOUR_PACKAGING', // FEDEX_BOX, FEDEX_PAK, FEDEX_TUBE, YOUR_PACKAGING, ...
            // Express US: FEDEX_BOX, FEDEX_ENVELOPE, FEDEX_PAK, FEDEX_TUBE, YOUR_PACKAGING
            'TotalWeight' => array('Value' => $totalWeight, 'Units' => 'LB'), // LB and KG
            'Shipper' => array(
                'Contact' => array(
                    'PersonName' => $v->getVendorAttn(),
                    'CompanyName' => $v->getVendorName(),
                    'PhoneNumber' => $v->getTelephone(),
                ),
                'Address' => array(
                    'StreetLines' => array($v->getStreet(1), $v->getStreet(2)),
                    'City' => $v->getCity(),
                    'StateOrProvinceCode' => $v->getRegionCode(),
                    'PostalCode' => $v->getZip(),
                    'CountryCode' => $v->getCountryId(),
                ),
            ),
            'Recipient' => array(
                'Contact' => array(
                    'PersonName' => $a->getName(),
                    'CompanyName' => $a->getCompany(),
                    'PhoneNumber' => $a->getTelephone(),
                ),
                'Address' => array(
                    'StreetLines' => array($a->getStreet(1), $a->getStreet(2)),
                    'City' => $a->getCity(),
                    'StateOrProvinceCode' => $a->getRegionCode(),
                    'PostalCode' => $a->getPostcode(),
                    'CountryCode' => $a->getCountryId(),
                    'Residential' => $isHomeDelivery ? true : $this->isResidentialDelivery($v, $track),
                ),
            ),
            'ShippingChargesPayment' => array(
                'PaymentType' => $v->getFedexPaymentType(), // RECIPIENT, SENDER and THIRD_PARTY
                'Payor' => $this->getShipServiceVersion('Payor')
            ),
            'RateRequestTypes' => array('ACCOUNT'), // ACCOUNT and LIST
            'PackageCount' => $this->getUdropshipPackageCount() ? $this->getUdropshipPackageCount() : 1,
            $keyRequestedPackages => array(
                '0' => array(
                    'SequenceNumber' => $this->getUdropshipPackageIdx() ? $this->getUdropshipPackageIdx() : 1,
                    'Weight' => array('Value' => $weight, 'Units' => 'LB'), // LB and KG
                    'Dimensions' => array(
                        'Length' => $length,
                        'Width' => $width,
                        'Height' => $height,
                        'Units' => $v->getDimensionUnits(),// valid values IN or CM
                    ),
                    'CustomerReferences' => array(
                        '0' => array('CustomerReferenceType' => 'CUSTOMER_REFERENCE', 'Value' => $this->_reference),
                        '1' => array('CustomerReferenceType' => 'INVOICE_NUMBER', 'Value' => $orderId),
                        '2' => array('CustomerReferenceType' => 'P_O_NUMBER', 'Value' => $poId)
                    ),// CUSTOMER_REFERENCE, INVOICE_NUMBER, P_O_NUMBER, SHIPMENT_INTEGRITY, STORE_NUMBER, BILL_OF_LADING
                )
            ),
            'LabelSpecification' => $this->getShipServiceVersion('LabelSpecification'),
        );
        if ($v->getFedexInsurance()) {
            $shipment[$keyRequestedPackages]['0']['InsuredValue'] = array('Amount' => $value, 'Currency' => $currencyCode);
        }
        if ($fedexData->getFedexSignatureOption()!='NO_SIGNATURE_REQUIRED') {
            $shipment[$keyRequestedPackages]['0']['SpecialServicesRequested']['SpecialServiceTypes'][] = 'SIGNATURE_OPTION';
            $shipment[$keyRequestedPackages]['0']['SpecialServicesRequested']['SignatureOptionDetail'] = array(
                'OptionType' => $fedexData->getFedexSignatureOption(),
                //'SignatureReleaseNumber' => '',
            );
        }
        if ($v->getFedexDryIceWeight()!=0){
            $shipment[$keyRequestedPackages]['0']['SpecialServicesRequested']['SpecialServiceTypes'] = array('DRY_ICE');
            $shipment[$keyRequestedPackages]['0']['SpecialServicesRequested']['DryIceWeight']['Units'] = 'KG';
            $shipment[$keyRequestedPackages]['0']['SpecialServicesRequested']['DryIceWeight']['Value'] = $v->getFedexDryIceWeight();
        }

        if (false) {
            $shipment['LabelSpecification']['CustomLabelDetail'] = array(
                'TextEntries' => array(
                    '0' => array(
                        'Position' => array('X'=>1, 'Y'=>1),
                        'Format' => '...',
                        'ThermalFontID' => 1 // 1..23
                    ),
                ),
                'GraphicEntries' => array(
                    '0' => array(
                        'Position' => array('X'=>1, 'Y'=>1),
                        'PrinterGraphicID' => '/sdfg/sdfg.png',
                    ),
                ),
            );
        }
        if (false) {
            $shipment['SpecialServicesRequested'] = array(
                'SpecialServiceTypes' => array('COD'),
                'CodDetail' => array('CollectionType' => 'ANY'), // ANY, GUARANTEED_FUNDS
                'CodCollectionAmount' => array('Amount' => 150, 'Currency' => 'USD')
            );
        }

        if ($this->hasUdropshipMasterTrackingId()) {
            $shipment['MasterTrackingId'] = array('TrackingNumber' => $this->getUdropshipMasterTrackingId());
        }

        if ($a->getCountryId()!=$v->getCountryId() || $v->getCountryId()=='IN') {

            $shipment['InternationalDetail'] = array(
                'DutiesPayment' => array(
                    'PaymentType' => $v->getFedexPaymentType(), // RECIPIENT, SENDER and THIRD_PARTY
                    'Payor' => $this->getShipServiceVersion('Payor')
                ),
                'DocumentContent' => 'NON_DOCUMENTS', //or 'DOCUMENTS_ONLY',
                'CustomsValue' => array('Amount' => $value, 'Currency' => $currencyCode),
            );
            if ($v->getFedexITN()) {
                $shipment['InternationalDetail']['ExportDetail']['ExportComplianceStatement'] = $v->getFedexItn();
            }
            $i = 0;
            $itemsQty = 0;
            $itemsDesc = array();
            $_itemsWeight = 0;
            foreach ($this->getMpsRequest('items') as $_item) {
                $item = $_item['item'];
                $_oItem = $item->getOrderItem() ? $item->getOrderItem() : $item;
                if ($_oItem->isDummy(true)) continue;
                $itemsDesc[] = $item->getName();
                $itemPrice = $item->getBasePrice() ? $item->getBasePrice() : $item->getPrice();
                $_weight = (!empty($_item['weight']) ? $_item['weight'] : $item->getWeight());
                $_itemsWeight += $_weight;
                $_qty = (!empty($_item['qty']) ? $_item['qty'] : $item->getQty());
                $itemsQty += $_qty;
                $shipment['InternationalDetail']['Commodities'][(string)$i++] = array(
                    'NumberOfPieces' => 1,
                    'Description' => $item->getName(),
                    'CountryOfManufacture' => $v->getCountryId(),
                    'Weight' => array('Value' => $_weight, 'Units' => 'LB'),
                    'Quantity' => $_qty,
                    'QuantityUnits' => 'EA',
                    'UnitPrice' => array('Amount' => $itemPrice, 'Currency' => $currencyCode),
                    'CustomsValue' => array('Amount' => $itemPrice*$_qty, 'Currency' => $currencyCode)
                );
            }
            if ($_itemsWeight>0) {
                $_divider = $weight/$_itemsWeight;
                if (!empty($shipment['InternationalDetail']['Commodities'])) {
                    foreach ($shipment['InternationalDetail']['Commodities'] as $__idx=>$__val) {
                        $__val['Weight']['Value'] = sprintf("%.2f", $__val['Weight']['Value']*$_divider);
                        $shipment['InternationalDetail']['Commodities'][$__idx]=$__val;
                    }
                }
            }
            $shipment['CustomsClearanceDetail'] = array(
                'CustomsValue' => array(
                    'Currency' => $currencyCode,
                    'Amount' => $value,
                ),
                'DutiesPayment' => array(
                    'PaymentType' => $v->getFedexPaymentType(), // RECIPIENT, SENDER and THIRD_PARTY
                    'Payor' => $this->getShipServiceVersion('Payor')
                )
            );
            $shipment['CustomsClearanceDetail']['Commodities'] = array(array(
                'Weight' => array(
                    'Units' => 'LB',
                    'Value' =>  $totalWeight
                ),
                'NumberOfPieces' => 1,
                'CountryOfManufacture' => $v->getCountryId(),
                'Description' => implode(', ', $itemsDesc),
                'Quantity' => ceil($itemsQty),
                'QuantityUnits' => 'pcs',
                'UnitPrice' => array(
                    'Currency' => $currencyCode,
                    'Amount' =>  $value
                ),
                'CustomsValue' => array(
                    'Currency' => $currencyCode,
                    'Amount' =>  $value
                ),
            ));
        }

        $nEmailsValid = $this->getValidNotifyEmails($v);
        $nTypesValid = $this->getValidNotifyTypes($v);

        if (!empty($nEmailsValid) && !empty($nTypesValid)) {
            $shipment['SpecialServicesRequested']['SpecialServiceTypes'] = 'EMAIL_NOTIFICATION';
            $neIdx = 0; foreach ($nEmailsValid as $_nEmail) {
                //$neIdx = (string)$neIdx;
                $shipment['SpecialServicesRequested']['EMailNotificationDetail']['Recipients'][$neIdx]['EMailNotificationRecipientType'] = 'OTHER';
                $shipment['SpecialServicesRequested']['EMailNotificationDetail']['Recipients'][$neIdx]['Localization']['LanguageCode'] = 'en';
                $shipment['SpecialServicesRequested']['EMailNotificationDetail']['Recipients'][$neIdx]['EMailAddress'] = $_nEmail;
                $shipment['SpecialServicesRequested']['EMailNotificationDetail']['Recipients'][$neIdx]['Format'] = 'TEXT';
                foreach ($nTypesValid as $_nType) {
                    if ($_nType == 'shipment') {
                        $shipment['SpecialServicesRequested']['EMailNotificationDetail']['Recipients'][$neIdx]['NotifyOnShipment'] = true;
                    } elseif ($_nType == 'exception') {
                        $shipment['SpecialServicesRequested']['EMailNotificationDetail']['Recipients'][$neIdx]['NotifyOnException'] = true;
                    } elseif ($_nType == 'delivery') {
                        $shipment['SpecialServicesRequested']['EMailNotificationDetail']['Recipients'][$neIdx]['NotifyOnDelivery'] = true;
                    }
                }
                if (++$neIdx>=6) break;
            }
        }

        if ($isSmartPost) {
            $shipment['SmartPostDetail'] = array(
                'Indicia' => ((float)$weight >= 1) ? 'PARCEL_SELECT' : 'PRESORTED_STANDARD',
                'AncillaryEndorsement' => 'ADDRESS_CORRECTION',
                'HubId' => $v->getFedexSmartpostHubid()
            );
        }

        $request = array(
            'WebAuthenticationDetail' => array(
                'UserCredential' => array(
                    'Key' => $v->getFedexUserKey(),
                    'Password' => $v->getFedexUserPassword(),
                )
            ),
            'ClientDetail' => array(
                'AccountNumber' => $v->getFedexAccountNumber(),
                'MeterNumber' => $v->getFedexMeterNumber(),
            ),
            'TransactionDetail' => array(
                'CustomerTransactionId' => '*** Express Domestic Shipping Request v6 using PHP ***'
            ),
            'Version' => array('ServiceId' => 'ship', 'Major' => $this->getShipServiceVersion('Major'), 'Intermediate' => $this->getShipServiceVersion('Intermediate'), 'Minor' => '0'),
            'RequestedShipment' => $shipment,
        );

        $client = $this->getSoapClient($v, 'ship');

        $response = $client->processShipment($request);

        if (isset($response->CompletedShipmentDetail->MasterTrackingId->TrackingNumber)) {
            $this->setUdropshipMasterTrackingId($response->CompletedShipmentDetail->MasterTrackingId->TrackingNumber);
        }

        /*
        Mage::helper('udropship')->dump($request, 'fedex_label');
        Mage::helper('udropship')->dump('REQUEST', 'fedex_label');
        Mage::helper('udropship')->dump($client->__getLastRequestHeaders(), 'fedex_label');
        Mage::helper('udropship')->dump($client->__getLastRequest(), 'fedex_label');
        Mage::helper('udropship')->dump('RESPONSE', 'fedex_label');
        Mage::helper('udropship')->dump($client->__getLastResponseHeaders(), 'fedex_label');
        Mage::helper('udropship')->dump($client->__getLastResponse(), 'fedex_label');
        */

        if ($response->HighestSeverity == 'FAILURE' || $response->HighestSeverity == 'ERROR') {
            $errors = array();
            if (is_array($response->Notifications)) {
                foreach ($response->Notifications as $notification) {
                    $errors[] = $notification->Severity . ': ' . $notification->Message;
                }
            } else {
                $errors[] = $response->Notifications->Severity . ': ' . $response->Notifications->Message;
            }
            Mage::throwException(join(', ', $errors));
        }

        $track->setCarrierCode($carrierCode);
        $track->setTitle($store->getConfig('carriers/'.$carrierCode.'/title'));
        if (isset($response->CompletedShipmentDetail->CompletedPackageDetails->TrackingId)) {
            $track->setNumber($response->CompletedShipmentDetail->CompletedPackageDetails->TrackingId->TrackingNumber);
        } else {
            $_trackingIds = $response->CompletedShipmentDetail->CompletedPackageDetails->TrackingIds;
            $_trackingNumber = null;
            if (is_array($_trackingIds)) {
                foreach ($_trackingIds as $_tId) {
                    $_trackingNumber = $_tId->TrackingNumber;
                }
            } else {
                $_trackingNumber = $_trackingIds->TrackingNumber;
            }
            $track->setNumber($_trackingNumber);
        }
        $track->setMasterTrackingId($this->getUdropshipMasterTrackingId());
        $track->setPackageCount($this->getUdropshipPackageCount() ? $this->getUdropshipPackageCount() : 1);
        $track->setPackageIdx($this->getUdropshipPackageIdx() ? $this->getUdropshipPackageIdx() : 1);

        if (!empty($response->CompletedShipmentDetail->ShipmentRating)) {
            $shipmentRateDetails = $response->CompletedShipmentDetail->ShipmentRating->ShipmentRateDetails;
            $finalPrice = null;
            if (is_array($shipmentRateDetails)) {
                $rates = array();
                foreach ($shipmentRateDetails as $details) {
                    $rates[$details->RateType] = $details->TotalNetCharge->Amount;
                }
                if (isset($rates['RATED_ACCOUNT'])) {
                    $finalPrice = $rates['RATED_ACCOUNT'];
                } elseif (isset($rates['PAYOR_ACCOUNT'])) {
                    $finalPrice = $rates['PAYOR_ACCOUNT'];
                } else {
                    $finalPrice = current($rates);
                }
            } else {
                $finalPrice = $shipmentRateDetails->TotalNetCharge->Amount;
            }
            $track->setFinalPrice($finalPrice);
        }
        //$track->setResultExtra(serialize($extra));

        $labelImages = array(
            base64_encode($response->CompletedShipmentDetail->CompletedPackageDetails->Label->Parts->Image),
            //$response->CompletedShipmentDetail->CodReturnDetail->Label->Parts->Image,
        );

        $labelModel = Mage::helper('udropship')->getLabelTypeInstance($v->getLabelType());
        $labelModel->setVendor($v)->updateTrack($track, $labelImages);
        return $this;
    }

    public function isResidentialDelivery($v, $track)
    {
        $residential = $v->getFedexResidential();
        $sAddr = $track->getShipment()->getOrder()->getShippingAddress();
        if ($sAddr->getIsCommercial()) {
            $residential = false;
        } elseif ($sAddr->getIsResidential()) {
            $residential = true;
        }
        return $residential;
    }

    public function voidLabel($track)
    {
        
    }
    
    public function refundLabel($track)
    {

    }

    public function processImage($type, $labelImage, $data=null)
    {
        return $labelImage;
        if ($type=='PDF') {
            return $labelImage;
        }

        $labelImage = base64_decode($labelImage);

        $extra = array();

        /*
        $descr = explode("\n", $this->getLabelDescr($data));
        foreach ($descr as $i=>$line) {
            if ($line) {
                $extra[] = 'A12,'.(1064+$i*22).',0,3,1,1,N,"'.addslashes($line).'"';
            }
        }
        */

        if (!is_null($data) && $this->getVendor()->getEplDoctab()) {
            $doctab = explode("\n", $this->getLabelDocTab($data));
            foreach ($doctab as $i=>$line) {
                if ($line) {
                    $extra[] = 'A12,'.(1295+$i*22).',0,3,1,1,N,"'.addslashes($line).'"';
                }
            }
        }

        $labelImage = preg_replace(array(
            '|\r\n|', '|^EPL2$|m', '|^ZB$|m', '|^P1$|m',
        ), array(
            "\n", "I8,A,001\nOD", "ZT", join("\n", $extra)."\nP1",
        ), $labelImage);

        for ($i=0, $s=''; $i<32; $i++) if ($i!=10) $s .= chr($i);
        $labelImage = strtr($labelImage, $s, str_pad('', 31, '*'));
#echo "<textarea style='width:100%; height:200px'>".$labelImage."</textarea>"; exit;

        return base64_encode($labelImage);
    }

    public function collectTracking($v, $trackIds)
    {
        $client = $this->getSoapClient($v, 'track');

        $request = array(
            'WebAuthenticationDetail' => array(
                'UserCredential' => array(
                    'Key' => $v->getFedexUserKey(),
                    'Password' => $v->getFedexUserPassword(),
                )
            ),
            'ClientDetail' => array(
                'AccountNumber' => $v->getFedexAccountNumber(),
                'MeterNumber' => $v->getFedexMeterNumber(),
            ),
            'TransactionDetail' => array(
                'CustomerTransactionId' => '*** Express Domestic Shipping Request v6 using PHP ***'
            ),
            'Version' => array('ServiceId' => 'trck', 'Major' => '4', 'Intermediate' => '0', 'Minor' => '0'),
            'PackageIdentifier' => array('Value' => null, 'Type' => 'TRACKING_NUMBER_OR_DOORTAG'),
        );

        $result = array();
        foreach ($trackIds as $trackId) {
            $request['PackageIdentifier']['Value'] = $trackId;
            $response = $client->track($request);
            $result[$trackId] = Unirgy_Dropship_Model_Source::TRACK_STATUS_PENDING;

            /*
            Mage::helper('udropship')->dump('REQUEST', 'fedex_track');
            Mage::helper('udropship')->dump($client->__getLastRequestHeaders(), 'fedex_track');
            Mage::helper('udropship')->dump($client->__getLastRequest(), 'fedex_track');
            Mage::helper('udropship')->dump('RESPONSE', 'fedex_track');
            Mage::helper('udropship')->dump($client->__getLastResponseHeaders(), 'fedex_track');
            Mage::helper('udropship')->dump($client->__getLastResponse(), 'fedex_track');
            */

            if (in_array($response->HighestSeverity, array('FAILURE', 'ERROR'))) {
                //possible that record doesn't exist yet
                continue;
            }
            if (!$response->TrackDetails || !$response->TrackDetails->StatusCode) {
                //unknown situation
                Mage::log(__METHOD__);
                Mage::log($response);
                continue;
            }
            $status = $response->TrackDetails->StatusCode;

            #$status = 'AB';
            if (in_array($status, array('AP'/*at pickup*/, 'EP'/*enroute to pickup*/, 'OC'/*order created*/))) {
                //record exists, but not picked up yet
                continue;
            }
            if (in_array($status, array('CA'/*cancelled*/))) {
                //cancel
                $result[$trackId] = Unirgy_Dropship_Model_Source::TRACK_STATUS_CANCELED;
                continue;
            }
            if (in_array($status, array('DL'/*delivered*/))) {
                //delivered
                $result[$trackId] = Unirgy_Dropship_Model_Source::TRACK_STATUS_DELIVERED;
                continue;
            }
            $result[$trackId] = Unirgy_Dropship_Model_Source::TRACK_STATUS_READY;
        }

        return $result;
    }

    public function useV12()
    {
        return Mage::getStoreConfigFlag('udropship/admin/fedex_use_v12');
    }
    public function getPayourField($field)
    {
        $v = $this->getVendor();
        if ($v->getFedexPaymentType()=='THIRD_PARTY') {
            switch ($field) {
                case 'AccountNumber':
                    $result = $v->getFedexThirdpartyAccountNumber();
                    break;
                case 'PersonName':
                    $result = $v->getFedexThirdpartyName();
                    break;
                case 'CompanyName':
                    $result = $v->getFedexThirdpartyCompany();
                    break;
                case 'PhoneNumber':
                    $result = $v->getFedexThirdpartyPhone();
                    break;
            }
        } else {
            switch ($field) {
                case 'AccountNumber':
                    $result = $v->getFedexAccountNumber();
                    break;
                case 'PersonName':
                    $result = $v->getVendorAttn();
                    break;
                case 'CompanyName':
                    $result = $v->getVendorName();
                    break;
                case 'PhoneNumber':
                    $result = $v->getTelephone();
                    break;
            }
        }
        return $result;
    }
    public function getShipServiceVersion($element)
    {
        $v = $this->getVendor();
        $useV12 = $this->useV12();
        switch ($element) {
            case 'filename':
                $result = $useV12 ? 'ShipService_v12' : 'ShipService_v6';
                break;
            case 'Intermediate':
                $result = $useV12 ? '1' : '0';
                break;
            case 'RequestedPackages':
                $result = $useV12 ? 'RequestedPackageLineItems' : 'RequestedPackages';
                break;
            case 'Payor':
                if ($useV12) {
                    $result = array(
                        'ResponsibleParty' => array(
                            'AccountNumber' => $this->getPayourField('AccountNumber'),
                            'Contact' => array(
                                'PersonName' => $this->getPayourField('PersonName'),
                                'CompanyName' => $this->getPayourField('CompanyName'),
                                'PhoneNumber' => $this->getPayourField('PhoneNumber'),
                            ),
                            /*
                            'Address' => array(
                                'StreetLines' => array($v->getStreet(1), $v->getStreet(2)),
                                'City' => $v->getCity(),
                                'StateOrProvinceCode' => $v->getRegionCode(),
                                'PostalCode' => $v->getZip(),
                                'CountryCode' => $v->getCountryId(),
                            ),
                            */
                        )
                    );
                } else {
                    $result = array(
                        'AccountNumber' => $this->getPayourField('AccountNumber'),
                        'CountryCode' => $v->getCountryId(),
                    );
                }
                break;
            case 'LabelSpecification':
                $result = array(
                    'LabelFormatType' => 'COMMON2D', // COMMON2D, LABEL_DATA_ONLY
                    'ImageType' => $v->getLabelType()=='EPL' ? 'EPL2' : 'PNG',  // DPL, EPL2, PDF, ZPLII and PNG
                    'LabelStockType' => $v->getFedexLabelStockType(),
                    'LabelPrintingOrientation' => $v->getPdfLabelRotate()==180 ? 'BOTTOM_EDGE_OF_TEXT_FIRST': 'TOP_EDGE_OF_TEXT_FIRST',
                );
                if ($useV12) {
                    $result['LabelRotation'] = ($v->getPdfLabelRotate()==90 ? 'LEFT' : ($v->getPdfLabelRotate()==270 ? 'RIGHT' : 'NONE'));
                }
                break;
            case 'Major':
            default:
                $result = $useV12 ? '12' : '6';
                break;
        }
        return $result;
    }

    /**
     * Get initialized SoapClient instance
     *
     * @param mixed $v vendor
     * @param mixed $service 'track' or 'ship'
     * @return SoapClient
     */
    public function getSoapClient($v, $service)
    {
        if ($v->getFedexTestMode()) {
            $wsdlOptions = array(
                'trace' => !!$v->getFedexTestMode(),
                'location' => "https://gatewaybeta.fedex.com/web-services/$service",

            );
        } else {
            $wsdlOptions = array(
                'cache_wsdl' => WSDL_CACHE_BOTH,
                'trace'      => true,
            );
        }

        $wsdlFile = Mage::getConfig()->getModuleDir('etc', 'Unirgy_Dropship').DS.'fedex'
            .DS.($service=='track' ? 'TrackService_v4' : $this->getShipServiceVersion('filename')).'.wsdl';

        $client = new SoapClient($wsdlFile, $wsdlOptions);

        return $client;
    }

    public function getValidNotifyEmails($v)
    {
        $nEmails = $v->getData('fedex_notify_email');
        if (empty($nEmails)) {
            $nEmails = array();
        } else {
            if (is_scalar($nEmails)) {
                $nEmails = array_filter(explode(',', $nEmails));
            }
            if (!is_array($nEmails)) {
                $nEmails = array();
            }
        }
        $nEmailsValid = array();
        foreach ($nEmails as $_nEmail) {
            $_nEmail = trim($_nEmail);
            if (Zend_Validate::is($_nEmail, 'EmailAddress')) {
                $nEmailsValid[] = $_nEmail;
            }
        }
        return $nEmailsValid;
    }
    public function getValidNotifyTypes($v)
    {
        $nTypes = $v->getData('fedex_notify_on');
        if (empty($nTypes)) {
            $nTypes = array();
        } else {
            if (is_scalar($nTypes)) {
                $nTypes = array_filter(explode(',', $nTypes));
            }
            if (!is_array($nTypes)) {
                $nTypes = array();
            }
        }
        $nTypesValidAll = array_filter(array_keys(
            Mage::getSingleton('udropship/source')->setPath('fedex_notify_on')->toOptionHash()
        ));
        $nTypesValid = array();
        foreach ($nTypes as $_nType) {
            if (in_array($_nType, $nTypesValidAll)) {
                $nTypesValid[] = $_nType;
            }
        }
        return $nTypesValid;
    }
}