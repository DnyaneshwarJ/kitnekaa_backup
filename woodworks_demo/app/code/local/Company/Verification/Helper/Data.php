<?php
class Company_Verification_Helper_Data extends Mage_Core_Helper_Abstract
{

    function sendOTP($mobileNumber)
    {
        $number_of_digits = 6;
        $val = substr ( number_format ( time () * mt_rand (), 0, '', '' ), 0, $number_of_digits );
        try {
            $url = 'http://www.smsjust.com/blank/sms/user/urlsms.php';
            $smsSender = curl_init ( $url );
            curl_setopt ( $smsSender, CURLOPT_POST, 1 );
            curl_setopt ( $smsSender, CURLOPT_POSTFIELDS, array (
                'username' => 'kitnekaa',
                'pass' => '123456',
                'senderid' => 'KTNEKA',
                'message' => $val .' is Your Kitnekaa OTP Code. Your OTP is usable once and will remain valid for 15 minutes.',
                'dest_mobileno' => $mobileNumber,
                'response' => 'Y'
            ) );
            curl_setopt ( $smsSender, CURLOPT_RETURNTRANSFER, true );
            $smsResponse = curl_exec ( $smsSender );
            curl_close ( $smsSender );
        } catch ( HttpException $ex ) {
            Mage::logException ( $ex );
        }

        return $val;
    }

}
	 