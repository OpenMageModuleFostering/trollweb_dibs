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

class Trollweb_Dibs_Model_Dibspw_Observer extends Mage_Core_Model_Abstract
{
    public function order_cancel_after(Varien_Event_Observer $observer) {
        $hdibs = Mage::helper('dibs');

        $order = $observer->getOrder();
        if ($order->getId()) {
            $dibspw = Mage::getModel('dibs/dibspw');
            $dibspw->cancel($order->getPayment());
        }
        
        return $this;
    }

    public function cancelOrdersAfterTimeout($schedule) {
        $hDipsPw = Mage::helper('dibs/dibspw');
        $definedTimeout = $hDipsPw->getConfigData('timeout');
        
        if ($definedTimeout < 0) {
            return $this;
        }
        $timeout = date('Y-m-d H:i:s', time()-($definedTimeout*60));

        $orders = Mage::getModel('sales/order')->getCollection()
            ->addFieldToFilter('updated_at', array('lt' => $timeout))
            ->addFieldToFilter('status', array('eq' => Trollweb_Dibs_Model_Dibspw::ORDER_STATUS_PENDING));

        foreach ($orders as $order) {
            $order->addStatusToHistory($order->getStatus(),Mage::helper('dibs')->__('Payment cancelled by system (timeout)'),false);
            $order->cancel()->save();
            $hDipsPw->dibsLog('Order cancellation due to timeout: Order Id: '. $order->getId());
        }
        
        return $this;
    }
    
    public function sales_order_load_after($event) {
        $order = $event->getOrder();
        if ($order->getId()) {
            if ($order->hasInvoices() AND $order->getPayment()->getData('method') == 'dibspw') {
                foreach ($order->getInvoiceCollection() as $invoice) {
                    if ($invoice->getStatus() != Mage_Sales_Model_Order_Invoice::STATE_CANCELED) {
                        $order->setActionFlag(Mage_Sales_Model_Order::ACTION_FLAG_INVOICE, true);
                        break;
                    }
                }
            }
        }
        return $this;
    }
    
    public function core_config_data_save_after($observer) {
        $configData = $observer->getEvent()->getConfigData();
        if ($configData && $configData->getPath() == 'payment/dibspw/test_mode' && $configData->getValue() == 0) {
            Mage::getModel('dibs/dibspw')->saveConfigData();
        }
        return $this;
    }
}