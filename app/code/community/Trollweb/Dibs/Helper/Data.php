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

class Trollweb_Dibs_Helper_Data extends Mage_Core_Helper_Abstract
{
    public $dibsCurrency = array(
        'DKK' => '208',
        'EUR' => '978',
        'USD' => '840',
        'GBP' => '826',
        'SEK' => '752',
        'AUD' => '036',
        'CAD' => '124',
        'ISK' => '352',
        'JPY' => '392',
        'NZD' => '554',
        'NOK' => '578',
        'CHF' => '756',
        'TRY' => '949',
    );
        
    public function getConfigData($path, $store_id = null) {
        return Mage::getStoreConfig('dibs/'.$path, $store_id);
    }

    public function validateCcTypes($ccTypes) {
        $val = preg_match('/^[a-zA-Z0-9\(\)\s,_-]+$/', $ccTypes);
        return $val;
    }

    public function escapeDelimiter($data) {
        $replacement = "\\".Trollweb_Dibs_Model_Dibspw_Cart::DELIMITER;
        return str_replace(Trollweb_Dibs_Model_Dibspw_Cart::DELIMITER, $replacement, $data);
    }

    public function escapeBreakline($str) {
        return str_replace("\n", " ", $str);
    }

    public function utf8Encoding($str) {
        return (mb_detect_encoding($str, 'UTF-8', true) && mb_check_encoding($str, 'UTF-8')) ? $str : utf8_encode($str);
    }

    public function getCurrenyCode($code) {
        
        return ($this->dibsCurrency[strtoupper($code)])?$this->dibsCurrency[strtoupper($code)]:'';
    }
    
    public function getCurrenyName($code) {
        $dibsCurrency = array_flip($this->dibsCurrency);
        return ($dibsCurrency[strtoupper($code)])?$dibsCurrency[strtoupper($code)]:'';
    }

    /**
     * Calculates MAC for given array of data.
     *
     * @param array $aData Array of data to calculate the MAC hash.
     * @param string $sHMAC HMAC key for hash calculation.
     * @param bool $bUrlDecode Flag if urldecode before MAC hash calculation is needed (for success action).
     * @return string
     */
    public function calcMAC($aData, $sHMAC, $bUrlDecode = FALSE) {
        $sMAC = '';
        if(!empty($sHMAC)) {
            $sData = '';
            if(isset($aData['MAC'])) unset($aData['MAC']);
            ksort($aData);
            foreach($aData as $sKey => $sVal) {
                $sData .= '&' . $sKey . '=' . (($bUrlDecode === TRUE) ? urldecode($sVal) : $sVal);
            }
             
            $sMAC = hash_hmac('sha256', ltrim($sData, '&'), $this->hextostr($sHMAC));
        }
        return $sMAC;
    }

    /**
     * Convert hex HMAC to string.
     *
     * @param string $sHMAC HMAC key for hash calculation.
     * @return string
     */
    private function hextostr($sHMAC) {
        $sRes = '';
        foreach(explode("\n", trim(chunk_split($sHMAC, 2))) as $h) $sRes .= chr(hexdec($h));
        return $sRes;
    }

    /**
     * Compare calculated MAC with MAC from response urldecode response if second parameter is TRUE.
     *
     * @param string $sHMAC
     * @param bool $bUrlDecode
     * @return bool
     */
    public function checkMAC($post, $sHMAC, $bUrlDecode = FALSE) {
        $post['MAC'] = isset($post['MAC']) ? $post['MAC'] : "";
        return ($post['MAC'] == $this->calcMAC($post, $sHMAC, $bUrlDecode)) ? TRUE : FALSE;
    }

    public function dibsLog($msg, $sendMail = FALSE, $onFile = false, $onLine = false) {
        if ($onFile) {
            $msg .= ' in '.$onFile;
        }
        if ($onLine) {
            $msg .= ' on line '.$onLine;
        }
        Mage::log($msg, null, 'trollweb_dibs.log');
    }

    public function getLogos() {
        return array(
            'green.jpg' => 'Green',
            'blue.jpg' => 'Blue',
            'yellow.jpg' => 'Yellow',
            'red.jpg' => 'Red',
            'grey.jpg' => 'Grey',
            'black.jpg' => 'Black',
        );
    }
}
