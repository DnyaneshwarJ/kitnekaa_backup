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

class Unirgy_Dropship_Model_Label_Endicia
    extends Varien_Object
    implements Unirgy_Dropship_Model_Label_Interface_Carrier
{
    protected $_mailClass = array(
        'Express Mail' => 'Express',
        'Express Mail Flat-Rate Envelope' => 'Express',
        'First-Class Mail' => 'First',
        'First-Class Mail Flat' => 'First',
        'First-Class Mail Parcel' => 'First',
        'Parcel Post' => 'ParcelPost',
        'Priority Mail' => 'Priority',
        'Express Mail International' => 'ExpressMailInternational',
        'First Class International' => 'FirstClassInternational',
        'Priority Mail International' => 'PriorityMailInternational',
    );

    protected $_mailpieceShape = array(
        //'' => 'Card',
        'First-Class Mail' => 'Letter',
        'First-Class Mail Flat' => 'Flat',
        'First-Class Mail Parcel' => 'Parcel',
        'Priority Mail Flat-Rate Box' => 'FlatRateBox',
        'Express Mail Flat-Rate Envelope' => 'FlatRateEnvelope',
        'Priority Mail Flat-Rate Envelope' => 'FlatRateEnvelope',
        'Priority Mail Large Flat-Rate Box' => 'LargeFlatRateBox',
        //'' => 'IrregularParcel',
        //'' => 'SmalFlatRateBox',
        //'' => 'LargeParcel',
        //'' => 'OversizedParcel',
    );

    public function requestLabel($track)
    {
        $hlp = Mage::helper('udropship');

        $v = $this->getVendor();

        $shipment = $track->getShipment();
        $order = $shipment->getOrder();
        if (!$order->getShippingMethod()) {
            return $this;
        }
        $this->setStore($order->getStore());

        $address = $order->getShippingAddress();
        $customer = $hlp->getOrderCustomer($order);

        $reference = $track->getReference() ? $track->getReference() : $order->getIncrementId();

        if (($shippingMethod = $shipment->getUdropshipMethod())) {
            $arr = explode('_', $shippingMethod);
            $methodCode = $arr[1];
        } else {
            $ship = explode('_', $order->getShippingMethod(), 2);
            $methodCode = $v->getShippingMethodCode($ship[1]);
        }
        //$usps = Mage::getSingleton('shipping/config')->getCarrierInstance('usps');
        $mailClass = !empty($this->_mailClass[$methodCode]) ? $this->_mailClass[$methodCode] : $v->getEndiciaMailClass();

        $skus = array();
        foreach ($shipment->getAllItems() as $item) {
            $skus[] = $item->getSku();
        }

        $weight = $track->getWeight();
        if (!$weight) {
            $weight = 0;
            foreach ($shipment->getAllItems() as $item) {
                $weight += $item->getWeight()*$item->getQty();
            }
        }
        $weight = max($weight, 1);

        $value = $track->getValue();
        if (!$value) {
            $value = 0;
            foreach ($shipment->getAllItems() as $item) {
                $value += ($item->getBasePrice() ? $item->getBasePrice() : $item->getPrice())*$item->getQty();
            }
        }

        $length = $track->getLength() ? $track->getLength() : $v->getDefaultPkgLength();
        $width = $track->getWidth() ? $track->getWidth() : $v->getDefaultPkgWidth();
        $height = $track->getHeight() ? $track->getHeight() : $v->getDefaultPkgHeight();

        $labelRotate = $v->getPdfLabelRotate() ? 'Rotate'.$v->getPdfLabelRotate() : 'None';
        $labelType = $v->getEndiciaLabelType();

        if ($v->getCountryId()!=$address->getCountryId()) {
            $labelType = 'International';
            $labelRotate = 'Rotate270';
        }

        $data = array(
            'RequesterID' => $v->getEndiciaRequesterId(),
            'AccountID' => $v->getEndiciaAccountId(),
            'PassPhrase' => $v->getEndiciaPassPhrase(),
            'MailClass' => $mailClass,
            'DateAdvance' => 0,
            'WeightOz' => $weight*16,
            'CostCenter' => 0,
            'Value' => $value,
            'InsuredValue' => $value,
            'MailpieceShape' => $v->getEndiciaMailpieceShape(),
            'MailpieceDimensions' => array(
                'Length' => $length,
                'Width' => $width,
                'Height' => $height,
            ),
            'Services' => array(
                'DeliveryConfirmation' => $v->getEndiciaDeliveryConfirmation() ? 'ON' : 'OFF',
                'SignatureConfirmation' => $v->getSignatureConfirmation() ? 'ON' : 'OFF',
                'ReturnReceipt' => $v->getEndiciaReturnReceipt() ? 'ON' : 'OFF',
                'ElectronicReturnReceipt' => $v->getEndiciaElectronicReturnReceipt() ? 'ON' : 'OFF',
                'COD' => $v->getEndiciaCod() ? 'ON' : 'OFF',
                'RestrictedDelivery' => $v->getEndiciaRestrictedDelivery(),
                'InsuredMail' => $v->getEndiciaInsuredMail(),
            ),
            'Description' => $reference,
            'PartnerCustomerID' => $customer->getIncrementId() ? $customer->getIncrementId() : 'Guest',
            'PartnerTransactionID' => $order->getIncrementId(),
            'ToName' =>  $address->getName(),
            'ToCompany' => $address->getCompany(),
            'ToAddress1' => $address->getStreet(1),
            'ToAddress2' => $address->getStreet(2),
            'ToAddress3' => $address->getStreet(3),
            'ToAddress4' => $address->getStreet(4),
            'ToCity' => $address->getCity(),
            'ToState' => $address->getRegionCode(),
            'ToPostalCode' => $address->getPostcode(),
            'ToZIP4' => '',
            'ToCountry' => $hlp->getCountryName($address->getCountryId()),
            'ToPhone' => $address->getTelephone() ? preg_replace('#[^0-9]#', '', $address->getTelephone()) : '8005551212',
            'FromName' => $v->getVendorName(),
            'ReturnAddress1' => $v->getStreet(1),
            'ReturnAddress2' => $v->getStreet(2),
            'ReturnAddress3' => $v->getStreet(3),
            'ReturnAddress4' => $v->getStreet(4),
            'FromCity' => $v->getCity(),
            'FromState' => $v->getRegionCode(),
            'FromPostalCode' => $v->getZip(),
            'FromZIP4' => '',
            'OriginCountry' => $hlp->getCountryName($v->getCountryId()),
            'FromPhone' => preg_replace('#[^0-9]#', '', $v->getTelephone()),
            'Test' => $v->getEndiciaTestMode() ? 'YES' : 'NO',
            'LabelType' => $labelType,
            'ImageRotation' => $labelRotate,
            'ResponseOptions' => array(
                'PostagePrice' => 'TRUE',
            ),
            'RubberStamp1' => 'Order # '.$order->getIncrementId(),
            'RubberStamp2' => $order->getIncrementId()!=$reference ? 'Ref. '.$reference : '',

            'CustomsFormType' => $v->getEndiciaCustomsFormType(),
            'CustomsQuantity1' => 0,
            'CustomsValue1' => 0,
            'CustomsWeight1' => 0,
            'CustomsQuantity2' => 0,
            'CustomsValue2' => 0,
            'CustomsWeight2' => 0,
            'CustomsQuantity3' => 0,
            'CustomsValue3' => 0,
            'CustomsWeight3' => 0,
            'CustomsQuantity4' => 0,
            'CustomsValue4' => 0,
            'CustomsWeight4' => 0,
            'CustomsQuantity5' => 0,
            'CustomsValue5' => 0,
            'CustomsWeight5' => 0,
        );
        switch ($v->getLabelType()) {
        case 'PDF':
            $data['ImageFormat'] = 'PNG';
            $data['LabelSize'] = '4x6';
            break;

        case 'EPL':
            $data['ImageFormat'] = 'EPL2';
            $data['LabelSize'] = '4x6';
            $data['LabelRotate'] = 'Rotate180';
/*
EPL2 and ZPLII are supported for:
- Default label type for domestic mail classes.
- International label type when used with
    - Priority Mail International Flat Rate Envelope
    - Small Flat Rate Box
    - First Class Mail International
*/
            break;

        default:
            Mage::throwException('Invalid vendor label type');
        }

        $client = new SoapClient($v->getEndiciaApiUrl().'?wsdl');

        $result = $client->GetPostageLabel(array('LabelRequest'=>$data));

        if (!$result || empty($result->LabelRequestResponse)) {
            Mage::throwException('Invalid API response');
        }
        $xml = $result->LabelRequestResponse;

        if ((int)$xml->Status != 0) {
            Mage::throwException($xml->ErrorMessage);
        }

        if (empty($xml->Base64LabelImage) && empty($xml->Label->Image)) {
            Mage::throwException('Unable to retrieve the label.');
        }

        $track->setCarrierCode('usps');
        $track->setTitle('USPS');
        $track->setNumber($xml->TrackingNumber);
        $track->setFinalPrice($xml->FinalPostage);
        $labelImages = array();

        $fees = $xml->PostagePrice->Fees;
        $extra = array(
            'batch' => $this->getBatch()->getId(),
            'ref' => $reference,
            'date' => strtoupper(date('M d Y')),
            'actwt' => $weight,
            'trkid' => $xml->TrackingNumber,
            'cur' => 'USD',
            'wunit' => 'LBS',
            'pkg' => 1, //TODO multiple pkg num
            'method' => $xml->PostagePrice->Postage->MailService,#$methodCode,
            'orderid' => $order->getIncrementId(),
            'value' => $value,
            'hndlfee' => $v->getHandlingFee(),
             // wordwrap items into 48 (55-7) chars max, and show only 6 first lines
            'items' => join("\n", array_slice(explode("\n", wordwrap(join(', ', $skus), 48, "\n", true)), 0, 6)),
            'svc' => $xml->PostagePrice->Postage->TotalAmount,
            'svcpub' => $xml->FinalPostage,
            'svcopt' => $fees->TotalAmount,
            'svccom' => $fees->CertificateOfMailing,
            'svccm' => $fees->CertifiedMail,
            'svccod' => $fees->CollectOnDelivery, //future
            'svcdc' => $fees->DeliveryConfirmation,
            'svcerr' => $fees->ElectronicReturnReceipt, //?
            'svcim' => $fees->InsuredMail,
            'svcrm' => $fees->RegisteredMail,
            'svcrd' => $fees->RestrictedDelivery, //future
            'svcrr' => $fees->ReturnReceipt,
            'svcrrm' => $fees->ReturnReceiptForMerchandise, //future
            'svcsc' => $fees->SignatureConfirmation,
            'svcsh' => $fees->SpecialHandling, //future
        );
        $extra['svctot'] = $extra['svcpub']+$extra['hndlfee'];

        if (!empty($xml->Base64LabelImage)) {
            $labelImages[] = $this->processImage($v->getLabelType(), $xml->Base64LabelImage, $extra);
        } else {
            foreach ($xml->Label->Image as $image) {
                $labelImages[] = $this->processImage($v->getLabelType(), $image->_, $extra);
            }
        }

        $labelModel = Mage::helper('udropship')->getLabelTypeInstance($v->getLabelType());
        $labelModel->setVendor($v)->updateTrack($track, $labelImages);
        if ($v->getLabelType()=='PDF' && $labelType=='International') {
            // for customs forms - renders on the whole page
            $track->setLabelRenderOptions(serialize(array(
                'r' => 90,
                'l' => .5,
                't' => .5,
                'w' => 10,
                'h' => 6.875,
            )));
        }

        return $this;
    }

    public function buyPostage($amount)
    {
        $amount = (float)$amount;
        $request = <<<EOT
recreditRequestXML=<RecreditRequest>
  <RequesterID>{$this->getRequesterId()}</RequesterID>
  <RequestID>{$this->getRequestId()}</RequestID>
  <CertifiedIntermediary>
    <AccountID>{$this->getAccountId()}</AccountID>
    <PassPhrase>{$this->getPassPhrase()}</PassPhrase>
  </CertifiedIntermediary>
  <RecreditAmount>{$amount}</RecreditAmount>
</RecreditRequest>
EOT;
        $xml = $this->_call('BuyPostageXML', 'RecreditRequestResponse', $request);

        if ((int)$xml->Status != 0) {
            echo "ERROR: ".$xml->ErrorMessage;
        }

        return true;
    }

    public function changePassPhrase($newPassPhrase)
    {
        $request = <<<EOT
changePassPhraseRequestXML=<ChangePassPhraseRequest>
  <RequesterID>{$this->getRequesterId()}</RequesterID>
  <RequestID>{$this->getRequestId()}</RequestID>
  <CertifiedIntermediary>
   <AccountID>{$this->getAccountId()}</AccountID>
   <PassPhrase>{$this->getPassPhrase()}</PassPhrase>
  </CertifiedIntermediary>
  <NewPassPhrase>{$newPassPhrase}</NewPassPhrase>
</ChangePassPhraseRequest>
EOT;
        $xml = $this->_call('ChangePassPhraseXML', 'ChangePassPhraseRequestResponse', $request);

        if ((int)$xml->Status != 0) {
            echo "ERROR: ".$xml->ErrorMessage;
        }

        return true;
    }

    public function refundLabel($track)
    {
        if ($trackIds instanceof Mage_Sales_Model_Order_Shipment_Track) {
            $trackIds = $trackIds->getLabelPic() ? $trackIds->getLabelPic() : $trackIds->getNumber();
        }
#print_r( $trackIds); exit;
        $request = <<<EOT
method=RefundRequest&XMLInput=<RefundRequest>
    <AccountID>{$this->getAccountId()}</AccountID>
    <PassPhrase>{$this->getPassPhrase()}</PassPhrase>
    <Test>N</Test>
    <RefundList>

EOT;
        foreach ((array)$trackIds as $id) {
            $request .= "       <PICNumber>$id</PICNumber>\n";
        }
        $request .= <<<EOT
    </RefundList>
</RefundRequest>

EOT;
#echo "<xmp>".$request."</xmp>";
        $xml = $this->_call('http://www.endicia.com/ELS/ELSServices.cfc', 'RefundResponse', $request);
#echo "<xmp>"; print_r($xml); exit;
        if ((int)$xml->Status != 0) {
            Mage::throwException("ERROR: ".$xml->ErrorMessage);
        }

        return true;
    }

    public function processImage($type, $labelImage, $data=null)
    {
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

    public function getLabelDescr($data)
    {
        $format = "\n\n\n".'Item No: %items$s';
        return Mage::helper('udropship')->vnsprintf($format, $data);
    }

    public function getLabelDocTab($data)
    {
        $format =
'BATCH# %batch$-6.6u  %date$-12.12s  ACT WT  %actwt$4.1f %wunit$-3.3s  #PK %pkg$-3.3u
SERVICE %method$-47.47s
TRACKING# %trkid$-27.27s  ALL CURRENCY %cur$-3.3s
ORDER NO.: %orderid$-20.20s  DV AMT %value$-9.2f
REFERENCE: %ref$s
COM %svccom$7.2f    CM  %svccm$7.2f    COD %svccod$7.2f    DC  %svcdc$7.2f
ERR %svcerr$7.2f    IM  %svcim$7.2f    RM  %svcrm$7.2f    RD  %svcrd$7.2f
RR  %svcrr$7.2f    RRM %svcrrm$7.2f    SC  %svcsc$7.2f    SH  %svcsh$7.2f
SHIPPING SERVICE %svc$7.2f    TOTAL CHARGE   %svcpub$7.2f

ITEMS: %items$s';

        $doctab = Mage::helper('udropship')->vnsprintf($format, $data);
#echo "<xmp>"; print_r($doctab); exit;
        return $doctab;
    }

    protected function _call($cmd, $responseTag, $request)
    {
        $url = strpos($cmd, 'http')===0 ? $cmd : ($this->getApiUrl().'/'.$cmd);

        $response = Mage::helper('udropship')->curlCall($url, $request);

        $response = preg_replace('#<'.$responseTag.'[^>]*>#', '<'.$responseTag.'>', $response);
        $xml = @simplexml_load_string($response);

        return $xml;
    }

    public function collectTracking($v, $trackIds)
    {
        return array();
    }
}