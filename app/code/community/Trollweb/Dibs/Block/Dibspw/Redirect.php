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

class Trollweb_Dibs_Block_Dibspw_Redirect extends Mage_Core_Block_Abstract
{
    protected $_form;

    public function initRequest() {
        $hdibs = Mage::helper('dibs');
        $dibspw = Mage::getModel('dibs/dibspw');

        $merchant = $dibspw->getConfigData('merchant');

        $order = $dibspw->getOrder();

        $dibsRequest = array();

        $isOrderVirtual = $order->getIsVirtual();

        if ($order->getBillingAddress()->getId()) {
            $billingAddress = $order->getBillingAddress();
        }
        if (!$isOrderVirtual AND $order->getShippingAddress()->getId()) {
            $shippingAddress = $order->getShippingAddress();
        }

        $optionalFields = $dibspw->getConfigData('optional_fields');
        if (!empty($optionalFields)) {
            $optionalFields = explode(',', $optionalFields);
        }

        $dibsRequest['merchant'] = $merchant;
        $dibsRequest['amount'] = sprintf("%0.0f",$order->getBaseGrandTotal()*100);
        $dibsRequest['orderId'] = $order->getIncrementId();
        $dibsRequest['currency'] = $hdibs->getCurrenyCode($order->getBaseCurrencyCode());
        $dibsRequest['acceptReturnUrl'] = Mage::getUrl('dibs/dibspw/accept', array('_secure'=>true));
        $dibsRequest['cancelReturnUrl'] = Mage::getUrl('dibs/dibspw/cancel', array('_secure'=>true));
        $dibsRequest['callbackUrl'] = Mage::getUrl('dibs/dibspw/callback', array('_secure'=>true)); //"http://enterprise.bjorneirik.trolldev.no/callback.php";

        if (isset($billingAddress) AND (is_array($optionalFields) AND in_array('billing', $optionalFields))) {
            $dibsRequest['billingFirstName'] = $hdibs->utf8Encoding($hdibs->escapeDelimiter($billingAddress->getFirstname()));
            $dibsRequest['billingLastName'] = $hdibs->utf8Encoding($hdibs->escapeDelimiter($billingAddress->getLastname()));
            $dibsRequest['billingAddress'] = $hdibs->utf8Encoding($hdibs->escapeDelimiter($hdibs->escapeBreakline($billingAddress->getStreetFull())));
            $dibsRequest['billingPostalCode'] = $billingAddress->getPostcode();
            $dibsRequest['billingPostalPlace'] = $hdibs->utf8Encoding($hdibs->escapeDelimiter($billingAddress->getCity()));
            $dibsRequest['billingEmail'] = $hdibs->utf8Encoding($hdibs->escapeDelimiter($billingAddress->getEmail()));
            $dibsRequest['billingMobile'] = $billingAddress->getTelephone();
        }
        if (isset($shippingAddress) AND (is_array($optionalFields) AND in_array('shipping', $optionalFields))) {
            $dibsRequest['shippingFirstName'] = $hdibs->utf8Encoding($hdibs->escapeDelimiter($shippingAddress->getFirstname()));
            $dibsRequest['shippingLastName'] = $hdibs->utf8Encoding($hdibs->escapeDelimiter($shippingAddress->getLastname()));
            $dibsRequest['shippingAddress'] = $hdibs->utf8Encoding($hdibs->escapeDelimiter($hdibs->escapeBreakline($shippingAddress->getStreetFull())));
            $dibsRequest['shippingPostalCode'] = $shippingAddress->getPostcode();
            $dibsRequest['shippingPostalPlace'] = $hdibs->utf8Encoding($hdibs->escapeDelimiter($shippingAddress->getCity()));
        }

        $dibsRequest['language'] = $dibspw->getConfigData('language');

        if ($hdibs->validateCcTypes($dibspw->getConfigData('cctypes'))) {
            $dibsRequest['payType'] = $dibspw->getConfigData('cctypes');
        }

        if ($dibspw->getConfigData('test_mode')) {
            $dibsRequest['test'] = 1;
        }

        if (is_array($optionalFields) AND in_array('cart', $optionalFields)) {
            $cart = Mage::getModel('dibs/dibspw_cart');
            $cartItems = $cart->setMageOrder($order)
                ->addItems()
                ->addShippingItem()
                ->getItems();
            $dibsRequest['oiTypes'] = $cart->getTypes();
            $dibsRequest['oiNames'] = $cart->getNames();
            	
            $rowCount = 1;
            foreach ($cartItems as $item) {
                $dibsRequest['oiRow'.$rowCount] = $cart->formatCartItem($item);
                $rowCount++;
            }
        }

        // Calculate the MAC for the form key-values to be posted to DIBS.
        $dibsRequest['MAC'] = $hdibs->calcMAC($dibsRequest, $dibspw->getConfigData('mac_key'));

        $this->_form = $this->getForm($dibsRequest);

        return $this;
    }

    public function getForm($dibsRequest) {
        $form = new Varien_Data_Form();
        $form->setId('dibs_dibspw_checkout')
            ->setAction(Trollweb_Dibs_Model_Dibspw::HTTP_REQUEST_URL)
            ->setMethod('POST')
            ->setUseContainer(true);

        foreach ($dibsRequest as $name => $value) {
            $form->addField($name,'hidden', array("name"=>$name, "value"=>$value));
        }

        $form->removeField('form_key');

        return $form;
    }

    protected function _toHtml() {
        $html = '<html><body>';
        $html.= $this->__('You will be redirected to Dibs in a few seconds.');
        $html.= $this->_form->toHtml();
        $html.= '<script type="text/javascript">document.getElementById("dibs_dibspw_checkout").submit();</script>';
        $html.= '</body></html>';

        return $html;
    }
}