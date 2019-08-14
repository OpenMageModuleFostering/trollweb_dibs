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

class Trollweb_Dibs_Model_Dibspw_Callback extends Trollweb_Dibs_Model_Dibspw
{
    public function acceptOrder($post) {
        $hdibs = Mage::helper('dibs');
        if (isset($post['orderId'])) {
            $orderId = $post['orderId'];
        }
        else {
            $orderId = 'n/a';
        }
        $hdibs->dibsLog('Accrept request for Order ID: ' . $orderId);
        
        return $this->saveTransactionData($post);
    }

    public function cancelOrder($post) {
        $hdibs = Mage::helper('dibs');
        $hdibspw = Mage::helper('dibs/dibspw');
        $order = $this->getOrder();
        
        if ($order->getStatus() == $hdibspw->getConfigData('order_status')) {
            return $this;
        }
        
        if ($hdibs->checkMAC($post, $this->getConfigData('mac_key', $order->getStoreId()))) {
            $order->addStatusToHistory($order->getStatus(),Mage::helper('dibs')->__('Payment cancelled by user'),false);
            $order->cancel()
                ->save();
            $this->getCheckout()
                ->setLoadInactive(true)
                ->getQuote()
                ->setIsActive(true)
                ->save();
        }
        return $this;
    }

    public function callback($post) {
        $hdibs = Mage::helper('dibs');
        if (isset($post['orderId'])) {
            $orderId = $post['orderId'];
        }
        else {
            $orderId = 'n/a';
        }
        $hdibs->dibsLog('Callback request for Order ID: ' . $orderId);
        
        return $this->saveTransactionData($post);
    }
    
    public function saveTransactionData($post) {
        $hdibs = Mage::helper('dibs');
        
        $order = $this->getOrder();
        if (!$order->getId()) {
            if (isset($post['orderId'])) {
                $order = Mage::getModel('sales/order')->loadByIncrementId($post['orderId']);
            }
        }

        if (!$order->getId()) {
            $hdibs->dibsLog('Unable to load order. POST params:');
            $hdibs->dibsLog($post);
            
            return $this;
        }
        
        $payment = $order->getPayment();
        if ($payment->getAdditionalInformation(Trollweb_Dibs_Model_Dibspw::INFO_TRANSACTION_ID)) {
            $hdibs->dibsLog('Order already have a DIBS transaction.');
            
            return $this;
        }
        
        if (isset($post['transaction'])) {
            $order->getPayment()
                ->setAdditionalInformation(Trollweb_Dibs_Model_Dibspw::INFO_TRANSACTION_ID,isset($post['transaction'])?$post['transaction']:'')
                ->setAdditionalInformation(Trollweb_Dibs_Model_Dibspw::INFO_STATUS,isset($post['status'])?$post['status']:'')
                ->setAdditionalInformation(Trollweb_Dibs_Model_Dibspw::INFO_ACQUIRER,isset($post['acquirer'])?$post['acquirer']:'')
                ->setAdditionalInformation(Trollweb_Dibs_Model_Dibspw::INFO_ACTION_CODE,isset($post['actionCode'])?$post['actionCode']:'')
                ->setAdditionalInformation(Trollweb_Dibs_Model_Dibspw::INFO_MERCHANT_ID,isset($post['merchant'])?$post['merchant']:'')
                ->setAdditionalInformation(Trollweb_Dibs_Model_Dibspw::INFO_DATE,Mage::getModel('core/date')->date())
                ->setAdditionalInformation(Trollweb_Dibs_Model_Dibspw::INFO_CURRENCY,isset($post['currency'])?$post['currency']:'')
                ->save();
        }
        
        if ($order->getState() == Mage_Sales_Model_Order::STATE_PENDING_PAYMENT) {
            if ($hdibs->checkMAC($post, $this->getConfigData('mac_key', $order->getStoreId()))) {
                if (isset($post['status']) AND ($post['status'] == Trollweb_Dibs_Model_Dibspw::CALLBACK_STATUS_ACCEPTED OR $post['status'] == Trollweb_Dibs_Model_Dibspw::CALLBACK_STATUS_PENDING)) {
                    if ($this->getCheckout()->hasQuote()) {
                        $this->getCheckout()
                            ->getQuote()
                            ->setIsActive(false)
                            ->save();
                    }
                    
                    $comment = $hdibs->__('Dibs Authorization successful');
                    $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING)
                        ->addStatusToHistory($this->getConfigData('order_status'),$comment)
                        ->setIsCustomerNotified(false)
                        ->save();
                        
                    $order->getPayment()
                        ->setStatus(self::STATUS_APPROVED)
                        ->save();
                    
                    if ($this->getConfigData('send_new_order_email')) {
                        $order->sendNewOrderEmail();
                    }
                }
                elseif ($post['status'] == Trollweb_Dibs_Model_Dibspw::CALLBACK_STATUS_DECLINED) {
                    $comment = $hdibs->__('Dibs Authorization Declined');
                    $order->cancel()
                        ->setState(Mage_Sales_Model_Order::STATE_CANCELED)
                        ->addStatusToHistory(Trollweb_Dibs_Model_Dibspw::ORDER_STATUS_DECLINED,$comment)
                        ->save();
                        
                    $order->getPayment()
                        ->setStatus(self::STATUS_DECLINED)
                        ->save();
                }
            }
            else {
                $comment = $hdibs->__('Order needs payment review');
                $order->setState(Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW)
                    ->addStatusToHistory($comment,Mage_Sales_Model_Order::STATUS_FRAUD)
                    ->save();
            }
        }
        else {
            $comment = 'Wrong order state ('.$order->getState().') on #'.$order->getIncrementId();
            $hdibs->dibsLog($comment);
            $hdibs->dibsLog($post);
        }
        
        if (!isset($post['transaction'])) {
            $this->getCheckout()->addError($hdibs->__('Payment is cancelled. Please try again.'));
        }
        elseif (isset($post['status']) AND !in_array($post['status'], array(Trollweb_Dibs_Model_Dibspw::CALLBACK_STATUS_PENDING, Trollweb_Dibs_Model_Dibspw::CALLBACK_STATUS_ACCEPTED))) {
            $hdibs->dibsLog('Payment failed on order: '.$order->getId().' DIBS AUTH Status: '.isset($post['status'])?$post['status']:'Unknown');
            $this->getCheckout()
                ->addError($hdibs->__('Payment failed, please try again.'));
            $order->cancel()
                ->save();
        }
        
        return $this;
    }
}
