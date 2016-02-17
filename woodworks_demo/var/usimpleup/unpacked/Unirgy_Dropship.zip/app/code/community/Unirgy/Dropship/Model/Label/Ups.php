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

class Unirgy_Dropship_Model_Label_Ups
    extends Mage_Usa_Model_Shipping_Carrier_Ups
    implements Unirgy_Dropship_Model_Label_Interface_Carrier
{
    const INT_FORM_TYPE_INVOICE  = '01';
    const INT_FORM_TYPE_SED      = '02';
    const INT_FORM_TYPE_CO       = '03';
    const INT_FORM_TYPE_NAFTA_CO = '04';

    protected $_track;
    protected $_reference;
    protected $_shipment;
    protected $_order;
    protected $_address;
    protected $_packages;

    protected $_packageType = array(
        '01' => 'UPS Letter',
        '02' => 'Customer Supplied Package',
        '03' => 'Tube',
        '04' => 'PAK',
        '21' => 'UPS Express Box',
        '2a' => 'UPS Small Express Box',
        '2b' => 'UPS Medium Express Box',
        '2c' => 'UPS Large Express Box',
        '24' => 'UPS 25KG Box',
        '25' => 'UPS 10KG Box',
        '30' => 'Pallet',
    );

    protected $_methodCode = array(
        '1DA'    => '01',
        '1DAL'   => '01',
        '1DAPI'  => '01',
        '2DA'    => '02',
        '2DAL'   => '02',
        'GND'    => '03',
        'GNDCOM' => '03',
        'GNDRES' => '03',
        'XPR'    => '07',
        'WXS'    => '07',
        'XPRL'   => '07',
        'XPD'    => '08',
        'STD'    => '11',
        '1DP'    => '13',
        '1DPL'   => '13',
        '3DS'    => '13',
        '1DM'    => '14',
        '1DML'   => '14',
        'XDM'    => '54',
        'XDML'   => '54',
        '2DM'    => '59',
        '2DML'   => '59',
    );

    public function requestLabel($track)
    {
        $hlp = Mage::helper('udropship');
        $this->_track = $track;

        $this->_shipment = $this->_track->getShipment();
        $this->_order = $this->_shipment->getOrder();
        $orderId = $this->_order->getIncrementId();

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

        $upsData = array();
        foreach (array(
            'ups_insurance',
            'ups_delivery_confirmation',
            'ups_verbal_confirmation',
            'ups_pickup',
            'ups_container',
            'ups_dest_type',
        ) as $upsKey) {
            $upsData[$upsKey] = $track->hasData($upsKey) ? $track->getData($upsKey) : $v->getData($upsKey);
        }
        $upsData = new Varien_Object($upsData);

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
        $weight = sprintf('%.1f', max($weight, .1));

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

        $length = $this->_track->getLength() ? $this->_track->getLength() : $v->getDefaultPkgLength();
        $width = $this->_track->getWidth() ? $this->_track->getWidth() : $v->getDefaultPkgWidth();
        $height = $this->_track->getHeight() ? $this->_track->getHeight() : $v->getDefaultPkgHeight();

        $a = $this->_address;

        $packageType = '02';

        if (($shippingMethod = $this->_shipment->getUdropshipMethod())) {
            $arr = explode('_', $shippingMethod);
            $methodCode = $arr[1];
        } else {
            $ship = explode('_', $this->_order->getShippingMethod(), 2);
            $methodCode = $v->getShippingMethodCode($ship[1]);
        }

        $serviceCode = $this->getCode('method_code', $methodCode);
        if (empty($serviceCode))  {
            $serviceCode = $methodCode;
        }
        // if UPS CGI is used
        if (!empty($this->_methodCode[$serviceCode])) {
            $serviceCode = $this->_methodCode[$serviceCode];
        }
        $services = $hlp->getCarrierMethods('ups');
        if (empty($services[$serviceCode])) {
            Mage::throwException('Invalid shipping method');
        }

        $fromState = $v->getRegionCode();
        $fromCountry = $v->getCountryId();
        $toCountry = $a->getCountryId();
        $toState = $a->getRegionCode();

        $weightUnit = $store->getConfig('carriers/ups/unit_of_measure');
        $shipperNumber = substr($v->getUpsShipperNumber() ? $v->getUpsShipperNumber() : $store->getConfig('carriers/ups/shipper_number'), 0, 6);

        $request = new Varien_Simplexml_Element('<ShipmentConfirmRequest/>');
        $request->setNode('Request/TransactionReference/CustomerContext', $orderId);
        $request->setNode('Request/TransactionReference/XpciVersion', '1.0001');
        $request->setNode('Request/RequestAction', 'ShipConfirm');
        $request->setNode('Request/RequestOption', 'nonvalidate');
        $request->setNode('Shipment/Description', $this->_reference);

        $request->setNode('Shipment/Shipper/Name', substr($v->getVendorName(), 0, 35));
        $request->setNode('Shipment/Shipper/AttentionName', substr($v->getVendorAttn() ? $v->getVendorAttn() : $v->getVendorName(), 0, 35));
        $request->setNode('Shipment/Shipper/ShipperNumber', $shipperNumber);
        $request->setNode('Shipment/Shipper/PhoneNumber', substr($v->getTelephone(), 0, 15));
        $request->setNode('Shipment/Shipper/EmailAddress', substr($v->getEmail(), 0, 50));
        $request->setNode('Shipment/Shipper/Address/AddressLine1', substr(trim($v->getStreet(1)), 0, 35));
        $request->setNode('Shipment/Shipper/Address/AddressLine2', substr(trim($v->getStreet(2)), 0, 35));
        $request->setNode('Shipment/Shipper/Address/AddressLine3', substr(trim($v->getStreet(3)), 0, 35));
        $request->setNode('Shipment/Shipper/Address/City', substr($v->getCity(), 0, 30));
        $request->setNode('Shipment/Shipper/Address/StateProvinceCode', $fromState);
        $request->setNode('Shipment/Shipper/Address/PostalCode', substr($v->getZip(), 0, 10));
        $request->setNode('Shipment/Shipper/Address/CountryCode', $fromCountry);

        $request->setNode('Shipment/ShipTo/CompanyName', substr($a->getCompany() ? $a->getCompany() : $a->getName(), 0, 35));
        $request->setNode('Shipment/ShipTo/AttentionName', substr($a->getName(), 0, 35));
        $request->setNode('Shipment/ShipTo/PhoneNumber', substr($a->getTelephone(), 0, 15));
        $request->setNode('Shipment/ShipTo/Address/AddressLine1', substr(trim($a->getStreet(1)), 0, 35));
        $request->setNode('Shipment/ShipTo/Address/AddressLine2', substr(trim($a->getStreet(2)), 0, 35));
        $request->setNode('Shipment/ShipTo/Address/AddressLine3', substr(trim($a->getStreet(3)), 0, 35));
        $request->setNode('Shipment/ShipTo/Address/City', substr($a->getCity(), 0, 30));
        $request->setNode('Shipment/ShipTo/Address/StateProvinceCode', $toState);
        $request->setNode('Shipment/ShipTo/Address/PostalCode', substr($a->getPostcode(), 0, 10));
        $request->setNode('Shipment/ShipTo/Address/CountryCode', $toCountry);
        if ($store->getConfig('carriers/ups/dest_type')=='RES') {
            $request->setNode('Shipment/ShipTo/Address/ResidentialAddress', '');
        }
        if (Mage::getStoreConfigFlag('carriers/ups/negotiated_rates', $store)) {
            $request->setNode('Shipment/NegotiatedRatesIndicator', '');
        }

        $request->setNode('Shipment/Service/Code', $serviceCode);
        $request->setNode('Shipment/Service/Description', $services[$serviceCode]);

        if ($packageType!='01' && ($fromCountry=='US') && (($toCountry=='CA') || ($toCountry=='US' && $toState=='PR')))  {
            $request->setNode('Shipment/InvoiceLineTotal/CurrencyCode', $currencyCode);
            $request->setNode('Shipment/InvoiceLineTotal/MonetaryValue', round($value));
        }
        if (($thirdPartyNumber = $v->getUpsThirdpartyAccountNumber())) {
            $request->setNode('Shipment/PaymentInformation/BillThirdParty/BillThirdPartyShipper/AccountNumber', $thirdPartyNumber);
            $request->setNode('Shipment/PaymentInformation/BillThirdParty/BillThirdPartyShipper/ThirdParty/Address/PostalCode', $v->getUpsThirdpartyPostcode());
            $request->setNode('Shipment/PaymentInformation/BillThirdParty/BillThirdPartyShipper/ThirdParty/Address/CountryCode', $v->getUpsThirdpartyCountry());
        } else {
            $request->setNode('Shipment/PaymentInformation/Prepaid/BillShipper/AccountNumber', $shipperNumber);
        }

        $request->setNode('Shipment/Package/Description', $this->_reference);
        $request->setNode('Shipment/Package/PackagingType/Code', $packageType);
        $request->setNode('Shipment/Package/PackagingType/Description', $this->_packageType[$packageType]);
        $request->setNode('Shipment/Package/Dimensions/UnitOfMeasure/Code', $v->getDimensionUnits());
        $request->setNode('Shipment/Package/Dimensions/Length', $length);
        $request->setNode('Shipment/Package/Dimensions/Width', $width);
        $request->setNode('Shipment/Package/Dimensions/Height', $height);
        $request->setNode('Shipment/Package/PackageWeight/UnitOfMeasurement/Code', $weightUnit);
        $request->setNode('Shipment/Package/PackageWeight/Weight', $weight);
        if ($fromCountry=='US' && $toCountry=='US') {
            $request->setNode('Shipment/Package/ReferenceNumber/Code', 'TN');
            $request->setNode('Shipment/Package/ReferenceNumber/Value', substr($this->_reference, 0, 35));
        }
        if ($upsData->getUpsDeliveryConfirmation()) {
            $request->setNode('Shipment/Package/PackageServiceOptions/DeliveryConfirmation/DCISType', $upsData->getUpsDeliveryConfirmation());
            $request->setNode('Shipment/Package/PackageServiceOptions/DeliveryConfirmation/DCISNumber', '');
        }
        if ($upsData->getUpsInsurance()) {
            //$request->setNode('Shipment/Package/PackageServiceOptions/InsuredValue/Type/Code', '01'); // 01-EVS, 02-DVS
            $request->setNode('Shipment/Package/PackageServiceOptions/InsuredValue/CurrencyCode', $currencyCode);
            $request->setNode('Shipment/Package/PackageServiceOptions/InsuredValue/MonetaryValue', round($value));
        }
        if ($upsData->getUpsVerbalConfirmation()) {
            $request->setNode('Shipment/Package/PackageServiceOptions/VerbalConfirmation/ContactInfo/Name', substr($store->getConfig('carriers/ups/shipper_attention'), 0, 35));
            $request->setNode('Shipment/Package/PackageServiceOptions/VerbalConfirmation/ContactInfo/PhoneNumber', substr($store->getConfig('carriers/ups/shipper_phone'), 0, 15));
        }
        if ($v->getUpsReleaseWithoutSignature()) {
            $request->setNode('Shipment/Package/PackageServiceOptions/ShipperReleaseIndicator', '');
        }

        switch ($v->getLabelType()) {
        case 'PDF':
            $request->setNode('LabelSpecification/LabelPrintMethod/Code', 'GIF');
            $request->setNode('LabelSpecification/LabelImageFormat/Code', 'GIF');
            $request->setNode('LabelSpecification/HTTPUserAgent', 'Mozilla/4.5');
            break;

        case 'EPL':
            $request->setNode('LabelSpecification/LabelPrintMethod/Code', 'EPL');
            $request->setNode('LabelSpecification/LabelStockSize/Height', '4');
            $request->setNode('LabelSpecification/LabelStockSize/Width', '8');
            break;

        default:
            Mage::throwException('Invalid vendor label type');
        }

        if ($fromCountry!=$toCountry) {
            $request->setNode('Shipment/SoldTo/CompanyName', substr($a->getCompany() ? $a->getCompany() : $a->getName(), 0, 35));
            $request->setNode('Shipment/SoldTo/AttentionName', substr($a->getName(), 0, 35));
            $request->setNode('Shipment/SoldTo/PhoneNumber', substr($a->getTelephone(), 0, 15));
            $request->setNode('Shipment/SoldTo/Address/AddressLine1', substr(trim($a->getStreet(1)), 0, 35));
            $request->setNode('Shipment/SoldTo/Address/AddressLine2', substr(trim($a->getStreet(2)), 0, 35));
            $request->setNode('Shipment/SoldTo/Address/AddressLine3', substr(trim($a->getStreet(3)), 0, 35));
            $request->setNode('Shipment/SoldTo/Address/City', substr($a->getCity(), 0, 30));
            $request->setNode('Shipment/SoldTo/Address/StateProvinceCode', $toState);
            $request->setNode('Shipment/SoldTo/Address/PostalCode', substr($a->getPostcode(), 0, 10));
            $request->setNode('Shipment/SoldTo/Address/CountryCode', $toCountry);
            if ($store->getConfig('carriers/ups/dest_type')=='RES') {
                $request->setNode('Shipment/SoldTo/Address/ResidentialAddress', '');
            }

            $request->setNode('Shipment/ShipmentServiceOptions/InternationalForms/FormType', self::INT_FORM_TYPE_INVOICE);
            $request->setNode('Shipment/ShipmentServiceOptions/InternationalForms/InvoiceNumber', $orderId);
            $request->setNode('Shipment/ShipmentServiceOptions/InternationalForms/InvoiceDate', date('Ymd', strtotime($this->_order->getCreatedAt())));
            $request->setNode('Shipment/ShipmentServiceOptions/InternationalForms/PurchaseOrderNumber', '');
            $request->setNode('Shipment/ShipmentServiceOptions/InternationalForms/TermsOfShipment', 'DDP');
            $request->setNode('Shipment/ShipmentServiceOptions/InternationalForms/ReasonForExport', 'SALE');
            $request->setNode('Shipment/ShipmentServiceOptions/InternationalForms/CurrencyCode', $currencyCode);
            $request->setNode('Shipment/ShipmentServiceOptions/InternationalForms/FreightCharges/MonetaryValue', sprintf('%.2f', $this->_order->getShippingAmount()));
            $root = $request->Shipment->ShipmentServiceOptions->InternationalForms;

            $number = 0;
            foreach ($this->getMpsRequest('items') as $item) {
                $item = is_array($item) ? $item['item'] : $item;
                $oItem = $item->getOrderItem();
                $requestProd = $root->addChild('Product');
                $description = $oItem->getName() . ($oItem->getDescription() != '' ? ' - '.$oItem->getDescription() : '').', Qty: '.(1*$item->getQty());
                $requestProd->setNode('Description', substr($description, 0, 35));
                $requestProd->setNode('OriginCountryCode', $fromCountry);
                $requestProd->setNode('CommodityCode', $oItem->getCommodityCode() ? $oItem->getCommodityCode()  : '');
                $requestProd->setNode('NumberOfPackagesPerCommodity', 1);
                $requestProd->setNode('Unit/Number', round($item->getQty()));
                $requestProd->setNode('Unit/Value', sprintf('%.4f', $oItem->getPrice()));
                $requestProd->setNode('Unit/UnitOfMeasurement/Code', 'PCS');
                $requestProd->setNode('ProductWeight/UnitOfMeasurement/Code', $weightUnit);
                $requestProd->setNode('ProductWeight/Weight', sprintf('%.1f', max($item->getWeight(), .1)));
            }
        }

        $xmlRequest = '<?xml version="1.0"?>'.$request->asNiceXml();
        $this->setXMLAccessRequest();

        $xmlResponse = $this->_callShippingXml('ShipConfirm', $xmlRequest);

        $response = new Varien_Simplexml_Element($xmlResponse);
        $this->_validateResponse($response);

        $shipmentDigest = (string)$response->descend('ShipmentDigest');
        if (!$shipmentDigest) {
            Mage::throwException("Could not retrieve shipment digest vaue.");
        }

        $xmlRequest =<<<EOT
<?xml version="1.0"?>
<ShipmentAcceptRequest>
    <Request>
         <TransactionReference>
              <CustomerContext>{$orderId}</CustomerContext>
              <XpciVersion>1.0001</XpciVersion>
         </TransactionReference>
         <RequestAction>ShipAccept</RequestAction>
    </Request>
    <ShipmentDigest><![CDATA[{$shipmentDigest}]]></ShipmentDigest>
</ShipmentAcceptRequest>
EOT;
        $xmlResponse = $this->_callShippingXml('ShipAccept', $xmlRequest);
        $response = new Varien_Simplexml_Element($xmlResponse);
#Mage::log($response);
        $this->_validateResponse($response);

        $xmlPackages = $response->descend("ShipmentResults/PackageResults");
        if (!$xmlPackages) {
            Mage::throwException('Could not retrieve shipping labels.');
        }

        $extra = array(
            'batch' => $this->getBatch()->getId(),
            'ref' => $this->_reference,
            'date' => strtoupper(date('M d Y')),
            'actwt' => $weight,
            'wunit' => (string)$response->descend("ShipmentResults/BillingWeight/UnitOfMeasurement/Code"),
            'pkg' => 1, //TODO multiple pkg num
            'method' => $services[$serviceCode],
            'billwt' => (string)$response->descend("ShipmentResults/BillingWeight/Weight"),
            'trkid' => (string)$response->descend("ShipmentResults/ShipmentIdentificationNumber"),
            'cur' => (string)$response->descend("ShipmentResults/ShipmentCharges/TransportationCharges/CurrencyCode"),
            'orderid' => $orderId,
            'value' => $value,
            'hndlfee' => $v->getHandlingFee(),
            'svc' => (string)$response->descend("ShipmentResults/ShipmentCharges/TransportationCharges/MonetaryValue"),
            'svcopt' => (string)$response->descend("ShipmentResults/ShipmentCharges/ServiceOptionsCharges/MonetaryValue"),
            'svcpub' => (string)$response->descend("ShipmentResults/ShipmentCharges/TotalCharges/MonetaryValue"),
            'svcneg' => (string)$response->descend("ShipmentResults/ShipmentCharges/NegotitatedRates/NetSummaryCharges/GrandTotal/MonetaryValue"),
             // wordwrap items into 48 (55-7) chars max, and show only 6 first lines
            'items' => join("\n", array_slice(explode("\n", wordwrap(join(', ', $skus), 48, "\n", true)), 0, 6)),
        );
        $extra['svctot'] = $extra['svcpub']+$extra['hndlfee'];

        $labelImages = array();

        foreach ($xmlPackages as $package) {
            $tracking = (string)$package->TrackingNumber;
            $labelImageFormat = (string)$package->descend('LabelImage/LabelImageFormat/Code');
            $labelImage = (string)$package->descend('LabelImage/GraphicImage');
            if ($labelImage) {
                $labelImages[] = $this->processImage($v->getLabelType(), $labelImage, $extra);
            }

            $intLabelImage = (string)$package->descend('LabelImage/InternationalSignatureGraphicImage');
            if ($intLabelImage) {
                $labelImages[] = $this->processImage($v->getLabelType(), $intLabelImage);
            }
            break;
        }


        $track->setCarrierCode('ups');
        $track->setTitle($store->getConfig('carriers/ups/title'));
        $track->setNumber($tracking);
        $track->setFinalPrice($extra['svctot']);
        $track->setResultExtra(serialize($extra));

        $labelModel = Mage::helper('udropship')->getLabelTypeInstance($v->getLabelType());
        $labelModel->setVendor($v)->updateTrack($track, $labelImages);
        return $this;
    }

    public function voidLabel($track)
    {
        if (!Mage::getStoreConfig('udropship/vendor/void_labels')) {
            return $this;
        }

        $this->_track = $track;
        $this->_shipment = $this->_track->getShipment();
        $this->_order = $this->_shipment->getOrder();
        $orderId = $this->_order->getIncrementId();

        $request = new Varien_Simplexml_Element('<VoidShipmentRequest/>');
        $request->setNode('Request/TransactionReference/CustomerContext', $orderId);
        $request->setNode('Request/TransactionReference/XpciVersion', '1.0');
        $request->setNode('Request/RequestAction', '1');
        $request->setNode('Request/RequestOption', '1');
        $request->setNode('ShipmentIdentificationNumber', $track->getNumber());

        $xmlRequest = '<?xml version="1.0"?>'.$request->asNiceXml();
        $this->setXMLAccessRequest();

        $xmlResponse = $this->_callShippingXml('Void', $xmlRequest);

        $response = new Varien_Simplexml_Element($xmlResponse);
        $this->_validateResponse($response, false);

        return $this;
    }

    public function refundLabel($track)
    {

    }

    protected function _validateResponse($response, $validateContext=true)
    {
        if (!$response) {
            Mage::throwException(Mage::helper('udropship')->__('Error parsing confirmation response'));
        }

        if ((int)$response->descend('Response/ResponseStatusCode') !== 1)  {
            Mage::throwException((string)$response->descend('Response/Error/ErrorCode').': '.(string)$response->descend('Response/Error/ErrorDescription'));
        }

        if ($validateContext) {
            $tref = (string)$response->descend('Response/TransactionReference/CustomerContext');
            if ($tref != $this->_order->getIncrementId())  {
                Mage::throwException("Transaction reference '".$tref."' received in response does not match transaction reference '".$this->_order->getIncrementId()."' that was sent in request.");
            }
        }
    }

    protected function _callShippingXml($call, $request)
    {
        $request = $this->_xmlAccessRequest.$request;
        $baseUrl = $this->getVendor()->getUpsApiUrl();
        if (!$baseUrl) {
            $baseUrl = Mage::getStoreConfig('carriers/ups/gateway_xml_url');
        }
        $baseUrl = preg_replace('#^(.*/ups\.app/xml).*$#', '$1', $baseUrl);

        $response = Mage::helper('udropship')->curlCall($baseUrl.'/'.$call, $request);
        #udDump($baseUrl.'/'.$call, '__ups_call');
        #udDump($request, '__ups_call');
        #udDump($response, '__ups_call');

        return $response;
    }

    public function processImage($type, $labelImage, $data=null)
    {
        $labelImage = base64_decode($labelImage);
        switch ($type) {
        case 'PDF':
            $tmp = Mage::getConfig()->getVarDir('label');
            $gifFile = tempnam($tmp, 'GIF');
            $pngFile = tempnam($tmp, 'PNG');

            file_put_contents($gifFile, $labelImage);

            if (!function_exists('imagepng')) {
                Mage::throwException('GD extension is required for GIF support');
            }
            if (!function_exists('imagecreatefromgif')) {
                Mage::throwException('GD has no GIF read support');
            }
            $im = imagecreatefromgif($gifFile);
            if(!$im) {
                Mage::throwException('Missing or incorrect image file: '.$gifFile);
            }

            imageinterlace($im, 0);
            $rotate = $this->getVendor()->getPdfLabelRotate();
            $im = imagerotate($im, (630-$rotate)%360, 0);

            if (!imagepng($im, $pngFile)) {
                Mage::throwException('Error while saving to PNG');
            }
            imagedestroy($im);

            $labelImage = file_get_contents($pngFile);

            @unlink($gifFile);
            @unlink($pngFile);
            break;

        case 'EPL':
            $extra = array();
/*
            if (!is_null($data)) {
                $descr = explode("\n", $this->getLabelDescr($data));
                foreach ($descr as $i=>$line) {
                    if ($line) {
                        $extra[] = 'A12,'.(1064+$i*22).',0,3,1,1,N,"'.addslashes($line).'"';
                    }
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

            $labelImage = preg_replace('|(\W)P1$|m', '$1'.join("\n", $extra)."\nP1", $labelImage);
            break;
        }

        return base64_encode($labelImage);
    }

    public function getLabelDescr($data)
    {
        $format = "\n\n\n".'Item No: %items$s';
        return Mage::helper('udropship')->vnsprintf($format, $data);
    }

    public function getLabelDocTab($data)
    {
/*
// original:

RE7018    APR 23, 2009       ACT WT   17.3 LBS  #PK 1
SERVICE STD                  BILL WT  18.0 LBS
TRACKING# 1ZA31T840394369846           ALL CURRENCY USD
ORDER NO.: 100000104
                                        DV AMT 199.00
HANDLING CHARGE 1.21                   FRT:SHP D&T:REC
SHIPMENT PUB RATE CHARGES:              SVC 28.19 USD
DV   1.95             COD   0.00           RS    0.00
DC   0.00             DGD   0.00
AH   0.00             PR    0.00           ROD   0.00
TOT PUB CHG  30.14              PUB+HANDLING    31.35

Item No: ABC123, DEF456";
*/
        $format =
'BATCH# %batch$-6.6u  %date$-12.12s  ACT WT  %actwt$4.1f %wunit$-3.3s  #PK %pkg$-3.3u
SERVICE %method$-20.20s BILL WT %billwt$4.1f %wunit$-3.3s
TRACKING# %trkid$-28.28s ALL CURRENCY %cur$-3.3s
ORDER NO.: %orderid$-20.20s  DV AMT %value$-9.2f
REFERENCE: %ref$s
SERVICE OPTIONS  %svcopt$7.2f
SHIPPING SERVICE %svc$7.2f    TOTAL CHARGE   %svcpub$7.2f

ITEMS: %items$s';

        $doctab = Mage::helper('udropship')->vnsprintf($format, $data);
        return $doctab;
    }

    public function collectTracking($v, $trackIds)
    {
        $this->setVendor($v);

        $request = new Varien_Simplexml_Element('<TrackRequest/>');
        $request->setNode('Request/TransactionReference/CustomerContext', '1');
        $request->setNode('Request/TransactionReference/XpciVersion', '1.0');
        $request->setNode('Request/RequestAction', 'Track');
        $request->setNode('Request/RequestOption', 'activity');

        $this->setXMLAccessRequest();

        $result = array();
        foreach ($trackIds as $trackId) {
            $request->setNode('TrackingNumber', $trackId);

            $xmlRequest = '<?xml version="1.0"?>'.$request->asNiceXml();

            $xmlResponse = $this->_callShippingXml('Track', $xmlRequest);

            $response = new Varien_Simplexml_Element($xmlResponse);
            try {
                $this->_validateResponse($response, false);

                $arr = $response->xpath("//Package/Activity/Status/StatusType");

                foreach ($arr as $a) {

                    $status = (string)$a->Code;

                    if (in_array($status, array('M'/*Manifest Pickup*/, 'P'/*Pickup*/, 'X'/*Exception*/))) {
                        //record exists, but not picked up yet
                        continue;
                    }
                    if (in_array($status, array('D'/*delivered*/))) {
                        //delivered
                        $result[$trackId] = Unirgy_Dropship_Model_Source::TRACK_STATUS_DELIVERED;
                        continue;
                    }
                    if (in_array($status, array('I'/*In Transit*/))) {
                        //In Transit
                        $result[$trackId] = Unirgy_Dropship_Model_Source::TRACK_STATUS_READY;
                        continue;
                    }
                    $result[$trackId] = Unirgy_Dropship_Model_Source::TRACK_STATUS_PENDING;
                }

            } catch (Exception $e) {
            }
        }

        return $result;
    }

    protected $_deliveryDatesCache=array();
    public function getDeliveryDateAll($vendor, $address, $weight, $value, $shipDate)
    {
        $reqKey = implode('-', array($vendor->getId(),$address->getId(),$weight,$value,$shipDate));
        if (isset($this->_deliveryDatesCache[$reqKey])) {
            return $this->_deliveryDatesCache[$reqKey];
        }
        $this->setVendor($vendor);
        $currencyCode = $address->getQuote()->getBaseCurrencyCode();
        $request = new Varien_Simplexml_Element('<TimeInTransitRequest/>');
        $request->setNode('Request/TransactionReference/CustomerContext', '1');
        $request->setNode('Request/TransactionReference/XpciVersion', '1.0');
        $request->setNode('Request/RequestAction', 'TimeInTransit');

        $request->setNode('TransitFrom/AddressArtifactFormat/PoliticalDivision1', $vendor->getRegionCode());
        $request->setNode('TransitFrom/AddressArtifactFormat/PoliticalDivision2', $vendor->getCity());
        $request->setNode('TransitFrom/AddressArtifactFormat/PostcodePrimaryLow', $vendor->getZip());
        $request->setNode('TransitFrom/AddressArtifactFormat/CountryCode', $vendor->getCountryId());

        $request->setNode('TransitTo/AddressArtifactFormat/PoliticalDivision1', $address->getRegionCode());
        $request->setNode('TransitTo/AddressArtifactFormat/PoliticalDivision2', $address->getCity());
        $request->setNode('TransitTo/AddressArtifactFormat/PostcodePrimaryLow', $address->getPostcode());
        $request->setNode('TransitTo/AddressArtifactFormat/CountryCode', $address->getCountryId());

        $request->setNode('PickupDate', date('Ymd', strtotime($shipDate)));

        $request->setNode('InvoiceLineTotal/CurrencyCode', $currencyCode);
        $request->setNode('InvoiceLineTotal/MonetaryValue', round($value,2));

        $request->setNode('ShipmentWeight/UnitOfMeasurement/Code', $address->getQuote()->getStore()->getConfig('carriers/ups/unit_of_measure'));
        $request->setNode('ShipmentWeight/Weight', $weight);

        $xmlRequest = '<?xml version="1.0"?>'.$request->asNiceXml();
        $this->setXMLAccessRequest();

        $xmlResponse = $this->_callShippingXml('TimeInTransit', $xmlRequest);

        $response = new Varien_Simplexml_Element($xmlResponse);
        $result = array();
        try {
            $this->_validateResponse($response, false);

            $arr = $response->xpath("//TimeInTransitResponse/TransitResponse/ServiceSummary");
            foreach ($arr as $a) {
                if (isset($a->Service->Code) && isset($a->EstimatedArrival->Date)) {
                    $result[(string)$a->Service->Code] = (string)$a->EstimatedArrival->Date;
                }
            }

        } catch (Exception $e) {
            $result = array();
        }

        $this->_deliveryDatesCache[$reqKey] = $result;
        return $result;
    }

    public function getDeliveryDate($vendor, $address, $method, $weight, $value, $shipDate)
    {
        $__sCode = explode('_', $method, 2);
        if ($__sCode[0]!='ups') return false;
        $__sCode = @$__sCode[1];
        if (!$__sCode) return false;
        $delDates = $this->getDeliveryDateAll($vendor, $address, $weight, $value, $shipDate);

        $result = false;
        $services = $this->_methodCode;
        foreach ($delDates as $_mCode => $_delDate) {
            if ($__sCode==$_mCode || $__sCode==@$services[(string)$_mCode]) {
                $result = $_delDate;
                break;
            }
        }

        return $result;
    }

}