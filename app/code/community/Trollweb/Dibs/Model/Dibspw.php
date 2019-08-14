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

class Trollweb_Dibs_Model_Dibspw extends Mage_Payment_Model_Method_Abstract
{
    protected $_infoBlockType = 'dibs/dibspw_paymentInfo';
    protected $_formBlockType = 'dibs/dibspw_form';

    const HTTP_REQUEST_URL = 'https://sat1.dibspayment.com/dibspaymentwindow/entrypoint';

    const ORDER_STATUS_PENDING = 'pending_dibs';
    const ORDER_STATUS_DECLINED = 'declined_dibs';
    
    const CALLBACK_STATUS_ACCEPTED = 'ACCEPTED';
    const CALLBACK_STATUS_ACCEPT = 'ACCEPT';
    const CALLBACK_STATUS_PENDING = 'PENDING';
    const CALLBACK_STATUS_CANCELLED = 'CANCELLED';
    const CALLBACK_STATUS_DECLINED = 'DECLINED';
    const CALLBACK_STATUS_DECLINE = 'DECLINE';
    const CALLBACK_STATUS_ERROR = 'ERROR';

    const INFO_TRANSACTION_ID = 'dibs_transaction_id';
    const INFO_STATUS = 'dibs_status';
    const INFO_ACQUIRER = 'dibs_acquirer';
    const INFO_ACTION_CODE = 'dibs_action_code';
    const INFO_MAC = 'dibs_mac';
    const INFO_MERCHANT_ID = 'dibs_merchant_id';
    const INFO_DATE = 'dibs_date';
    const INFO_CURRENCY = 'dibs_currency';
    

    protected $_oiRow = array();

    protected $_code = 'dibspw';
    protected $_canReviewPayment = true;
    protected $_isGateway = true;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = true;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canVoid = true;
    protected $_canUseInternal = false;
    protected $_canUseCheckout = true;
    protected $_canUseForMultishipping = false;
    protected $_canSaveCc = false;
    protected $_isInitializeNeeded = true;

    public function authorize(Varien_Object $payment, $amount) {
        return $this;
    }
    
    public function capture(Varien_Object $payment, $amount) {
        $hdibs = Mage::helper('dibs');

        if (!$payment->getAdditionalInformation(self::INFO_TRANSACTION_ID)) {
            Mage::throwException($hdibs->__('Could not find transaction id.'));
        }
        $dibsTransactionId = $payment->getAdditionalInformation(self::INFO_TRANSACTION_ID);

        $amount = (sprintf("%0.0f",$amount*100));

        $request = Mage::getModel('dibs/dibspw_api_request')
            ->initRequest()
            ->buildCaptureRequest($payment, $amount);
        $result = $request->send();

        $declineReason = $result->getData('declineReason')?$result->getData('declineReason'):'Unknown';
        switch ($result->getStatus()) {
            case self::CALLBACK_STATUS_ACCEPT:
            case self::CALLBACK_STATUS_PENDING:
                $payment->setStatus(Mage_Payment_Model_Method_Abstract::STATUS_APPROVED);
                if (!$payment->getParentTransactionId() || $dibsTransactionId != $payment->getParentTransactionId()) {
                    $payment->setTransactionId($dibsTransactionId);
                }
                break;
            case self::CALLBACK_STATUS_DECLINE:
                $hdibs->dibsLog('Payment capturing was delined. Order ID: '. $payment->getOrder()->getId(). ' DIBS Reason: '.$declineReason);
                Mage::throwException($hdibs->__('Payment capturing was delined, try again or capture offline and manually run capture in DIBS Admin panel'));
                break;
            case self::CALLBACK_STATUS_ERROR:
                $hdibs->dibsLog('Payment capturing error. Order ID: '. $payment->getOrder()->getId(). ' DIBS Reason: '.$declineReason);
                Mage::throwException($hdibs->__('Payment capturing was delined, try again or capture offline and manually run capture in DIBS Admin panel'));
                break;
            default:
                $hdibs->dibsLog('Payment capturing error (unknown). Order ID: '. $payment->getOrder()->getId(). ' DIBS Reason: '.$declineReason);
                Mage::throwException($hdibs->__('Error capturing payment, try again or capture offline and manually run capture in DIBS Admin panel'));
                break;
        }
        return $this;
    }

    public function refund(Varien_Object $payment, $amount) {
        $hdibs = Mage::helper('dibs');
         
        $amount = sprintf("%0.0f",$amount*100);
        $dibsTransactionId = $payment->getAdditionalInformation(self::INFO_TRANSACTION_ID);

        $request = Mage::getModel('dibs/dibspw_api_request')
        ->initRequest()
        ->buildRefundRequest($payment, $amount);
        $result = $request->send();

        $declineReason = $result->getData('declineReason')?$result->getData('declineReason'):'Unknown';
        switch ($result->getStatus()) {
            case self::CALLBACK_STATUS_ACCEPT:
                $payment->setStatus(Mage_Payment_Model_Method_Abstract::STATUS_APPROVED);
                break;
            case self::CALLBACK_STATUS_DECLINE:
                $hdibs->dibsLog('Payment declined. Order ID: '.$payment->getOrder()->getId() . ' DIBS Reason: '.$declineReason);
                $this->_getSession()->addError($hdibs->__('Refund was declined by DIBS. Use DIBS Admin panel to manually refund this transaction'));
                break;
            case self::CALLBACK_STATUS_ERROR:
                $hdibs->dibsLog('Payment refund error. Order ID: '.$payment->getOrder()->getId() . ' DIBS Reason: '.$declineReason);
                $this->_getSession()->addError($hdibs->__('Unable to refund DIBS transaction. Use DIBS Admin panel to manually refund this transaction'));
                break;
            default:
                $hdibs->dibsLog('Payment refund (unknown) error. Order ID: '.$payment->getOrder()->getId() . ' DIBS Reason: '.$declineReason);
                $this->_getSession()->addError($hdibs->__('Unable to refund DIBS transaction. Use DIBS Admin panel to manually refund this transaction'));
                break;
        }
        return $this;
    }

    public function cancel(Varien_Object $payment) {
        $hdibs = Mage::helper('dibs');

        $dibsTransactionId = $payment->getAdditionalInformation(self::INFO_TRANSACTION_ID);

        $request = Mage::getModel('dibs/dibspw_api_request')
            ->initRequest()
            ->buildCancelRequest($payment)
            ->validateData();
        if($request === false) {
            return $this;
        }
        $result = $request->send();

        $declineReason = $result->getData('declineReason')?$result->getData('declineReason'):'Unknown';
        switch ($result->getStatus()) {
            case self::CALLBACK_STATUS_ACCEPT:
                $payment->setStatus(Mage_Payment_Model_Method_Abstract::STATUS_VOID);
                break;
            case self::CALLBACK_STATUS_DECLINE:
                $hdibs->dibsLog('Payment cancel declined. Order ID: '.$payment->getOrder()->getId() . ' DIBS Reason: '.$declineReason);
                $this->_getSession()->addError($hdibs->__('Unable to cancel DIBS transaction. Use DIBS Admin panel to manually cancel this transaction'));
                break;
            case self::CALLBACK_STATUS_ERROR:
                $hdibs->dibsLog('Payment cancel error. Order ID: '.$payment->getOrder()->getId() . ' DIBS Reason: '.$declineReason);
                $this->_getSession()->addError($hdibs->__('Unable to cancel DIBS transaction. Use DIBS Admin panel to manually cancel this transaction'));
                break;
            default:
                $hdibs->dibsLog('Payment cancel (unknown) error. Order ID: '.$payment->getOrder()->getId() . ' DIBS Reason: '.$declineReason);
                $this->_getSession()->addError($hdibs->__('Unable to cancel DIBS transaction. Use DIBS Admin panel to manually cancel this transaction'));
                break;
        }
        return $this;
    }

    public function initialize($paymentAction, $stateObject) {
        $state = Mage_Sales_Model_Order::STATE_PENDING_PAYMENT;
        $stateObject->setState($state);
        $stateObject->setStatus(self::ORDER_STATUS_PENDING);
    }

    public function getCheckout() {
        return Mage::getSingleton('checkout/session');
    }

    public function acceptPayment(Mage_Payment_Model_Info $payment) {
        parent::acceptPayment($payment);
        return true;
    }

    public function denyPayment(Mage_Payment_Model_Info $payment) {
        parent::acceptPayment($payment);
        return true;
    }

    public function getRequest() {
        $request = Mage::getModel('dibs/dibspw_api_request');
        return $request;
    }

    public function getOrder() {
        $orderId = $this->getCheckout()->getLastOrderId();
        $order = Mage::getModel('sales/order')->load($orderId);

        return $order;
    }

    public function getRedirectText() {
        return $this->getConfigData('redirect_text');
    }

    public function getLogo() {
        return $this->getConfigData('dibs_logo');
    }

    public function getDibsRequestUrl() {
        return Trollweb_Dibs_Model_Dibspw::HTTP_REQUEST_URL;
    }

    public function getOrderPlaceRedirectUrl() {
        return Mage::getUrl('dibs/dibspw/redirect', array('_secure' => true));
    }

    protected function _getSession() {
        return Mage::getSingleton('adminhtml/session');
    }

    public function getTitle() {
        return $this->getConfigData('title');
    }

    public function saveConfigData() {
        $client = new Zend_Http_Client();
        $client->setUri('http://serial.trollweb.no/dibs.php');

        $client->setConfig(array(
            'adapter'   => 'Zend_Http_Client_Adapter_Curl',
            'curloptions' => array(
                CURLOPT_USERAGENT      => 'Zend_Curl_Adapter',
                CURLOPT_HEADER         => 0,
                CURLOPT_VERBOSE        => 0,
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_TIMEOUT        => 10,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSLVERSION      => 3,
            ),
        ));
        
        $client->setMethod(Zend_Http_Client::POST);
        
        $url = Mage::app()->getWebsite()->getConfig('web/unsecure/base_url');
        $domain = trim(preg_replace('/^.*?\\/\\/(.*)?\\//', '$1', $url));
        
        $storeName = Mage::app()->getStore()->getFrontendName();
        
        $client->setParameterPost('domain', $domain);
        $client->setParameterPost('ip', Mage::helper('core/http')->getRemoteAddr());
        $client->setParameterPost('merchant', $this->getConfigData('merchant'));
        $client->setParameterPost('customer', $storeName);
        
        try {
            $response = $client->request();
        }
        catch (Exception $e) {
            Mage::helper('dibs')->dibsLog($e->getMessage());
        }
        return $this;
    }
}