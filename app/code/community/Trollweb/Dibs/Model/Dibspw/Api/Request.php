<?php
/**
 * DIBS Payment module
 *
 * LICENSE AND USAGE INFORMATION
 * It is NOT allowed to modify, copy or re-sell this file or any
 * part of it. Please contact us by email at support@trollweb.no or
 * visit us at www.trollweb.no if you have any questions about this.
 * Trollweb is not responsible for any problems caused by this file.
 *
 * Visit us at http://www.trollweb.no today!
 *
 * @category   Trollweb
 * @package    Trollweb_Dibs
 * @copyright  Copyright (c) 2013 Trollweb (http://www.trollweb.no)
 * @license    Single-site License
 *
 */

class Trollweb_Dibs_Model_Dibspw_Api_Request extends Varien_Object
{
    const CAPTURE = 'capture';
    const CANCEL = 'cancel';
    const REFUND = 'refund';
    
    const URL_CAPTURE = 'https://api.dibspayment.com/merchant/v1/JSON/Transaction/CaptureTransaction';
    const URL_CANCEL = 'https://api.dibspayment.com/merchant/v1/JSON/Transaction/CancelTransaction';
    const URL_REFUND = 'https://api.dibspayment.com/merchant/v1/JSON/Transaction/RefundTransaction';

    protected $_client;

    public function initRequest() {
        $this->_client = new Zend_Http_Client();

//        $this->_client->setConfig(array(
//            'maxredirects'=>0,
//            'timeout'=>30,
//        ));
        
        $this->_client->setConfig(array(
            'adapter'   => 'Zend_Http_Client_Adapter_Curl',
            'curloptions' => array(
                CURLOPT_USERAGENT      => 'Zend_Curl_Adapter',
                CURLOPT_HEADER         => 0,
                CURLOPT_VERBOSE        => 0,
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_TIMEOUT        => 10,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSLVERSION      => 1,
            ),
        ));
        
        $this->_client->setHeaders(array('Content-Type: text/xml'));
        $this->_client->setMethod(Zend_Http_Client::POST);
        
        return $this;
    }
    
    public function buildCaptureRequest(Varien_Object $payment, $amount) {
        $hdibs = Mage::helper('dibs');
        $hdibspw = Mage::helper('dibs/dibspw');
        
        $this->setType(self::CAPTURE);
        
        $this->_client->setUri(self::URL_CAPTURE);
        
        $requestData = array();
        $requestData['merchantId'] = $payment->getAdditionalInformation(Trollweb_Dibs_Model_Dibspw::INFO_MERCHANT_ID);
        $requestData['amount'] = $amount;
        $requestData['transactionId'] = $payment->getAdditionalInformation(Trollweb_Dibs_Model_Dibspw::INFO_TRANSACTION_ID);
        
        $requestData['MAC'] = $hdibs->calcMAC($requestData, $hdibspw->getConfigData('mac_key'));
        
        $this->_client->setParameterPost('request', Zend_Json::encode($requestData));
        
        return $this;
    }
    
    public function buildCancelRequest(Varien_Object $payment) {
        $hdibs = Mage::helper('dibs');
        $hdibspw = Mage::helper('dibs/dibspw');
        
        $this->setType(self::CANCEL);
        
        $this->_client->setUri(self::URL_CANCEL);
        
        $requestData = array();
        $requestData['merchantId'] = $payment->getAdditionalInformation(Trollweb_Dibs_Model_Dibspw::INFO_MERCHANT_ID);
        $requestData['transactionId'] = $payment->getAdditionalInformation(Trollweb_Dibs_Model_Dibspw::INFO_TRANSACTION_ID);
        
        $requestData['MAC'] = $hdibs->calcMAC($requestData, $hdibspw->getConfigData('mac_key'));

        $this->_client->setParameterPost('request', Zend_Json::encode($requestData));
        
        return $this;
    }
    
    public function buildRefundRequest(Varien_Object $payment, $amount) {
        $hdibs = Mage::helper('dibs');
        $hdibspw = Mage::helper('dibs/dibspw');
        
        $this->setType(self::REFUND);
        
        $this->_client->setUri(self::URL_REFUND);
        
        $requestData = array();
        $requestData['merchantId'] = $payment->getAdditionalInformation(Trollweb_Dibs_Model_Dibspw::INFO_MERCHANT_ID);
        $requestData['amount'] = $amount;
        $requestData['transactionId'] = $payment->getAdditionalInformation(Trollweb_Dibs_Model_Dibspw::INFO_TRANSACTION_ID);
        
        $requestData['MAC'] = $hdibs->calcMAC($requestData, $hdibspw->getConfigData('mac_key'));
        
        $this->setRequestData($requestData);
        
        $this->_client->setParameterPost('request', Zend_Json::encode($requestData));
        
        return $this;
    }
    
    public function send() {
        $hdibs = Mage::helper('dibs');
        $result = Mage::getModel('dibs/dibspw_api_result');
        
        try {
            $response = $this->_client->request();
        } catch (Exception $e) {
            Mage::throwException($hdibs->__('Capture request failed, try again or contact site administation'));
        }

        $responseBody = Zend_Json::decode($response->getBody());
        $result->setData($responseBody);
        
        return $result;
    }
    
    public function validate() {
        $regcode = $this->getRegCode();
        $carray = explode(".",$_SERVER[base64_decode('U0VSVkVSX05BTUU=')]);
        $d = strtolower($carray[count($carray)-2]);

        return $this->checkLicense($regcode,$_SERVER[base64_decode('U0VSVkVSX05BTUU=')]);
    }

    protected function checkLicense($serial,$domain) {
        $mKey = "dHJvbGx3ZWJfZGlicw==";
        $secret = ${base64_decode('ZG9tYWlu')};
        $carray = explode('.',trim($domain));
        $regcode = $serial;
        if (count($carray) < 2) {
            $carray = array(uniqid(),uniqid());
        }

        $domain_array = array(
              'ao','ar','au','bd','bn','co','cr','cy','do','eg','et','fj','fk','gh','gn','id','il','jm','jp','kh','kw','kz','lb','lc','lr','ls',
              'mv','mw','mx','my','ng','ni','np','nz','om','pa','pe','pg','py','sa','sb','sv','sy','th','tn','tz','uk','uy','va','ve','ye','yu',
              'za','zm','zw'
              );
              $key = $secret.$regcode.$domain.serialize($domain_array);

              $tld = trim($carray[count($carray)-1]);
              if (in_array($tld,$domain_array)) {
                  $darr = array_splice($carray,-3);
              }
              else {
                  $darr = array_splice($carray,-2);
              }

              $d = strtolower(join(".",$darr));
              $secret = $d;
              $offset = 0;
              $privkey = rand(1,strlen($domain));
              $offset = (strlen($key)*32)-(strlen($key)*64)+$privkey-$offset+(strlen($key)*32);
              $f = base64_decode("c2hhMQ==");
              return ($f(base64_encode(strtolower(substr($secret,0,strlen($d) % $offset).substr($d,(strlen($secret) % $offset))).base64_decode(${base64_decode('bUtleQ==')}))) == ${base64_decode('cmVnY29kZQ==')});
    }
    
    public function validateData() {
        if (!is_array($this->getRequestData())) {
            return false;
        }
        $requestData = $this->getRequestData();
        $unionRequiredFields = array('merchantId','transactionId','MAC');
        
        if ($this->getType() == self::CAPTURE) {
            foreach (array_merge($unionRequiredFields, array('amount')) as $requiredField) {
                if (!isset($requestData[$requiredField])) {
                    return false;
                }
                elseif (empty($requestData[$requiredField])) {
                    return false;
                }
            }
        }
        elseif ($this->getType() == self::CANCEL) {
            foreach (array_merge($unionRequiredFields, array()) as $requiredField) {
                if (!isset($requestData[$requiredField])) {
                    return false;
                }
                elseif (empty($requestData[$requiredField])) {
                    return false;
                }
            }
        }
        elseif ($this->getType() == self::REFUND) {
            foreach (array_merge($unionRequiredFields, array('amount')) as $requiredField) {
                if (!isset($requestData[$requiredField])) {
                    return false;
                }
                elseif (empty($requestData[$requiredField])) {
                    return false;
                }
            }
        }
        return true;
    }
}
