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

class Trollweb_Dibs_Model_Source_Taxtype
{
    public function toOptionArray() {
        $arr = array();
        foreach ($this->getTaxTypes() as $k=>$v) {
            $arr[] = array('value'=>$v, 'label'=>$k);
        }
        return $arr;
    }

    public function getTaxTypes() {
        $hdibs = Mage::helper('dibs');
        return array(
            $hdibs->__(Trollweb_Dibs_Model_Dibspw_Cart::NAME_VATAMOUNT) => Trollweb_Dibs_Model_Dibspw_Cart::NAME_VATAMOUNT,
            $hdibs->__(Trollweb_Dibs_Model_Dibspw_Cart::NAME_VATPERCENT) => Trollweb_Dibs_Model_Dibspw_Cart::NAME_VATPERCENT,
        );
    }
}
