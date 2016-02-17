<?php

class Unirgy_Dropship_Model_Email extends Mage_Core_Model_Email_Template
{
    public function send($email, $name=null, array $variables = array())
    {
        if(!$this->isValidForSend()) {
            return false;
        }

        if (is_null($name)) {
            $name = substr($email, 0, strpos($email, '@'));
        }

        $variables['email'] = $email;
        $variables['name'] = $name;

        ini_set('SMTP', Mage::getStoreConfig('system/smtp/host'));
        ini_set('smtp_port', Mage::getStoreConfig('system/smtp/port'));

        $mail = $this->getMail();

        if (defined('Mage_Core_Model_Email_Template::XML_PATH_SENDING_SET_RETURN_PATH')) {
            $setReturnPath = Mage::getStoreConfig(self::XML_PATH_SENDING_SET_RETURN_PATH);
            switch ($setReturnPath) {
                case 1:
                    $returnPathEmail = $this->getSenderEmail();
                    break;
                case 2:
                    $returnPathEmail = Mage::getStoreConfig(self::XML_PATH_SENDING_RETURN_PATH_EMAIL);
                    break;
                default:
                    $returnPathEmail = null;
                    break;
            }

            if ($returnPathEmail !== null) {
                $mailTransport = new Zend_Mail_Transport_Sendmail("-f".$returnPathEmail);
                Zend_Mail::setDefaultTransport($mailTransport);
            }
        }

        if (is_array($email)) {
            foreach ($email as $emailOne) {
                $mail->addTo($emailOne, $name);
            }
        } else {
            $mail->addTo($email, '=?utf-8?B?'.base64_encode($name).'?=');
        }

        if (!empty($variables['_BCC'])) {
            $bcc = $variables['_BCC'];
            if (is_string($bcc)) {
                $bcc = explode(',', $bcc);
            }
            foreach ($bcc as $e) {
                $mail->addBcc($e);
            }
        }
        if (!empty($variables['_ATTACHMENTS'])) {
            foreach ((array)$variables['_ATTACHMENTS'] as $a) {
                if (is_string($a)) {
                    $a = array('filename'=>$a);
                }
                if (empty($a['content']) && (empty($a['filename']) || !is_readable($a['filename']))) {
                    Mage::throwException('Invalid attachment data: '.print_r($a, 1));
                }
                $at = $mail->createAttachment(
                    !empty($a['content']) ? $a['content'] : file_get_contents($a['filename']),
                    !empty($a['type']) ? $a['type'] : Zend_Mime::TYPE_OCTETSTREAM,
                    !empty($a['disposition']) ? $a['disposition'] : Zend_Mime::DISPOSITION_ATTACHMENT,
                    !empty($a['encoding']) ? $a['encoding'] : Zend_Mime::ENCODING_BASE64,
                    basename($a['filename'])
                );
            }
        }

        $this->setUseAbsoluteLinks(true);
        $text = $this->getProcessedTemplate($variables, true);

        if($this->isPlain()) {
            $mail->setBodyText($text);
        } else {
            $mail->setBodyHTML($text);
        }

        $mail->setSubject('=?utf-8?B?'.base64_encode($this->getProcessedTemplateSubject($variables)).'?=');
        $mail->setFrom($this->getSenderEmail(), $this->getSenderName());

        try {
            Mage::helper('udropship')->addToQueue($mail);
            //$mail->send(); // Zend_Mail warning..
            $this->_mail = null;
        }
        catch (Exception $e) {
            return false;
        }

        return true;
    }
}