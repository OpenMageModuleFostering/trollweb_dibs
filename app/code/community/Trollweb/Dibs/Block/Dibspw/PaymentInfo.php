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

class Trollweb_Dibs_Block_Dibspw_PaymentInfo extends Mage_Payment_Block_Info
{
    protected function _construct() {
        parent::_construct();
        $this->setTemplate('dibs/dibspw/paymentinfo.phtml');
    }

    protected function getLogoUrl() {
        return $this->getSkinUrl('images/trollweb/dibs/logos/'.$this->getMethod()->getLogo());
    }
    
    protected function _prepareSpecificInformation($transport = null) {
        $transport = parent::_prepareSpecificInformation($transport);
        $payment = $this->getInfo();
        $dibsInfo = Mage::getModel('dibs/dibspw_info');
        if (!$this->getIsSecureMode()) {
            $info = $dibsInfo->getPaymentInfo($payment);
        } else {
          $info = $dibsInfo->getPublicPaymentInfo($payment);
        }
        if (!$dibsInfo->hasTransaction()) {
            return $transport;
        }
        return $transport->addData($info);
    }
}