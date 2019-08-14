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

class Trollweb_Dibs_Block_Dibspw_Form extends Mage_Payment_Block_Form
{
    protected function _construct() {
        $this->setTemplate('dibs/dibspw/form.phtml');
        parent::_construct();
    }
    
    protected function getLogoUrl() {
        return $this->getSkinUrl('images/trollweb/dibs/logos/'.$this->getMethod()->getLogo());
    }
    
    protected function getRedirectText() {
        return $this->getMethod()->getRedirectText();
    }
}