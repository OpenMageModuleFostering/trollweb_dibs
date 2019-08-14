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

class Trollweb_Dibs_Model_Dibspw_Info
{
    protected $_hasTransaction = false;
    
    protected $_publicMap = array(
        //'transaction_id',
        //'status',
        //'date',
        //'currency',
    );

    protected $_secureMap = array(
        'transaction_id',
        'status',
        'date',
        'currency',
    );

    public function getPublicPaymentInfo($payment) {
        return $this->_makeMap($this->_publicMap,$payment);
    }

    public function getPaymentInfo($payment) {
        return $this->_makeMap($this->_secureMap,$payment);
    }

    protected function _makeMap($map,$payment) {
        $result = array();
        foreach ($map as $key) {
            $result[$this->_getLabel($key)] = $this->_getValue($key,$payment);
        }

        return $result;
    }

    protected function _getLabel($key) {
        switch ($key) {
            case 'transaction_id':
                return Mage::helper('dibs')->__('Transaction id');
            case 'status':
                return Mage::helper('dibs')->__('Transaction status');
            case 'date':
                return Mage::helper('dibs')->__('Transaction date');
            case 'currency':
                return Mage::helper('dibs')->__('Transaction currency');
        }
    }

    protected function _getValue($key,$payment) {
        switch ($key) {
            case 'transaction_id':
                $value = $payment->getAdditionalInformation('dibs_'.$key);
                $this->_hasTransaction = ($value)?true:false;
                break;
            case 'status':
                $value = Mage::helper('dibs')->__($payment->getAdditionalInformation('dibs_'.$key));
                break;
            case 'date':
                $value = Mage::helper('core')->formatDate($payment->getAdditionalInformation('dibs_'.$key),Mage_Core_Model_Locale::FORMAT_TYPE_LONG) . ' ' . Mage::helper('core')->formatTime($payment->getAdditionalInformation('dibs_'.$key),Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);
                break;
            case 'currency':
                $value = Mage::helper('dibs')->getCurrenyName($payment->getAdditionalInformation('dibs_'.$key));
                break;
        }

        if (!$value) {
            $value = '';
        }
        
        return $value;
    }
    
    public function hasTransaction() {
        return $this->_hasTransaction;
    }
}
