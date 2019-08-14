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

class Trollweb_Dibs_Model_Dibspw_Cart
{
    protected $_items;

    protected $_order;

    protected $_areItemsValid = true;

    const UNIT_CODE = 'pcs';

    const TYPE_QUANTITY = 'QUANTITY';
    const TYPE_UNITCODE = 'UNITCODE';
    const TYPE_DESCRIPTION = 'DESCRIPTION';
    const TYPE_AMOUNT = 'AMOUNT';
    const TYPE_ITEMID = 'ITEMID';
    const TYPE_VATAMOUNT = 'VATAMOUNT';
    const TYPE_VATPERCENT = 'VATPERCENT';

    const NAME_QUANTITY = 'Items';
    const NAME_UNITCODE = 'UnitCode';
    const NAME_DESCRIPTION = 'Description';
    const NAME_AMOUNT = 'Amount';
    const NAME_ITEMID = 'ItemId';
    const NAME_VATAMOUNT = 'VatAmount';
    const NAME_VATPERCENT = 'VatPercent';

    const DELIMITER = ";";

    public function setMageOrder($order) {
        $this->_order = $order;
        
        return $this;
    }

    public function addItems($bypassValidation = false) {
        $this->_render();
        if (!$bypassValidation && !$this->_areItemsValid) {
            return false;
        }
        
        return $this;
    }

    public function getItems() {
        return $this->_items;
    }

    protected function _render() {
        foreach ($this->_order->getAllItems() as $item) {
            if (!$item->getParentItem()) {
                $this->addItem($item);
            }
        }
    }

    public function addItem($item) {
        $hdibspw = Mage::helper('dibs/dibspw');
        if (!$item->getItemId()) {
            $this->_areItemsValid = false;
            return;
        }
        $item = new Varien_Object(array(
            'items'   => (int)$item->getQtyOrdered(),
            'unit_code'    => self::UNIT_CODE,
            'description' => $item->getName(),
        	'amount' => sprintf("%0.0f",$item->getBasePrice()*100), 
        	'item_id' => $item->getSku(),
        	'vat_amount' => ($hdibspw->getConfigData('cart_tax_type') == self::NAME_VATPERCENT)?sprintf("%0.0f",$item->getTaxPercent()*100):sprintf("%0.0f",$item->getBaseTaxAmount()*100), 
        ));

        $this->_items[] = $item;
        
        return $this;
    }

    public function addShippingItem() {
        $hdibspw = Mage::helper('dibs/dibspw');
        $item = new Varien_Object(array(
            'items' => 1,
            'unit_code'=> self::UNIT_CODE,
            'description' => $this->_order->getShippingDescription(),
        	'amount' => sprintf("%0.0f",$this->_order->getBaseShippingAmount()*100), 
        	'item_id' => 'Shipping',
            'vat_amount' => ($hdibspw->getConfigData('cart_tax_type') == self::NAME_VATPERCENT)?($this->_order->getBaseShippingTaxAmount() / $this->_order->getBaseShippingAmount() * 100)*100:sprintf("%0.0f",$this->_order->getBaseShippingTaxAmount()*100), 
        ));
        $this->_items[] = $item;
        
        return $this;
    }

    public function getTypes() {
        $hdibspw = Mage::helper('dibs/dibspw');
        
        $taxType = self::TYPE_VATAMOUNT;
        if ($hdibspw->getConfigData('cart_tax_type') == self::NAME_VATPERCENT) {
            $taxType = self::TYPE_VATPERCENT;
        }
        
        return self::TYPE_QUANTITY . self::DELIMITER .
            self::TYPE_UNITCODE . self::DELIMITER .
            self::TYPE_DESCRIPTION . self::DELIMITER .
            self::TYPE_AMOUNT . self::DELIMITER .
            self::TYPE_ITEMID . self::DELIMITER .
            $taxType;
    }

    public function getNames() {
        $hdibspw = Mage::helper('dibs/dibspw');

        $taxName = self::NAME_VATAMOUNT;
        if ($hdibspw->getConfigData('cart_tax_type') == self::NAME_VATPERCENT) {
            $taxName = self::NAME_VATPERCENT;
        }
        
        return self::NAME_QUANTITY . self::DELIMITER .
            self::NAME_UNITCODE . self::DELIMITER .
            self::NAME_DESCRIPTION . self::DELIMITER .
            self::NAME_AMOUNT . self::DELIMITER .
            self::NAME_ITEMID . self::DELIMITER .
            $taxName;
    }

    public function formatCartItem($item) {
        $value = '';
        foreach ($item->getData() as $rowValue) {
            $value .= $rowValue . self::DELIMITER;
        }
        return substr($value, 0, -1);
    }
}