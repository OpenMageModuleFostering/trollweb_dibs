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

class Trollweb_Dibs_Model_Source_Language
{
    public function toOptionArray() {
        $arr = array();
        foreach ($this->getLanguages() as $k=>$v) {
            $arr[] = array('value'=>$k, 'label'=>$v);
        }
        return $arr;
    }

    public function getLanguages() {
        $hdibs = Mage::helper('dibs');
        return array(
            'en_US' => 'English (US)',
            'en_GB' => 'English (GB)',
            'da_DK' => 'Danish',
            'sv_SE' => 'Swedish',
            'nb_NO' => 'Norwegian (Bokmål)',
        );
    }
}
