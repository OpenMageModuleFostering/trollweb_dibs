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

class Trollweb_Dibs_DibspwController extends Mage_Core_Controller_Front_Action
{
    protected function _expireAjax() {
        if (!Mage::getSingleton('checkout/session')->getQuote()->hasItems()) {
            $this->getResponse()->setHeader('HTTP/1.1','403 Session Expired');
            exit;
        }
    }

    public function acceptAction() {
        $hdibspw = Mage::helper('dibs/dibspw');
        
        $post = $this->getRequest()->getParams();
        $dibspw = Mage::getModel('dibs/dibspw_callback')->acceptOrder($post);

        if ($dibspw->getOrder()->getStatus() == $hdibspw->getConfigData('order_status')) {
            $this->_redirect('checkout/onepage/success', array('_secure'=>true));
        }
        else {
            $this->_redirect('/', array('_secure'=>true));
        }
    }

    public function cancelAction() {
        $post = $this->getRequest()->getParams();
        $dibspw = Mage::getModel('dibs/dibspw_callback')->cancelOrder($post);

        if ($dibspw->getOrder()->isCanceled()) {
            $session = Mage::getSingleton('checkout/session');
            $session->setQuoteId($session->getDibspwQuoteId());
            $this->_redirect('checkout/cart', array('_secure'=>true));
        }
        else {
            $this->_redirect('/', array('_secure'=>true));
        }
    }

    public function redirectAction() {
        $session = Mage::getSingleton('checkout/session');
        $session->setDibspwQuoteId($session->getQuoteId());
        if (!$session->getLastRealOrderId()) {
            $this->_redirect('/', array('_secure'=>true));
            return;
        }
        $this->getResponse()->setBody($this->getLayout()->createBlock('dibs/dibspw_redirect')->initRequest()->toHtml());
        $session->unsQuoteId();
        $session->unsRedirectUrl();
    }

    public function callbackAction() {
        $post = $this->getRequest()->getParams();
        $callback = Mage::getModel('dibs/dibspw_callback')->callback($post);
    }
}
