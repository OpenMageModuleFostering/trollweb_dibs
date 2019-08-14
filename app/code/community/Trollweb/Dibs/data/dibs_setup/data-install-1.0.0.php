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

/** @var $installer Mage_Sales_Model_Entity_Setup */
$installer = $this;

/**
 * Install order statuses from config
 */
$data     = array(
    array(
        'status' => 'pending_dibs',
        'label' => 'Pending dibs'
    ),
    array(
        'status' => 'declined_dibs',
        'label' => 'Declined dibs'
    ),
);

foreach ($statuses as $code => $info) {
    $data[] = array(
        'status' => $code,
        'label'  => $info['label']
    );
}
$installer->getConnection()->insertArray(
    $installer->getTable('sales/order_status'),
    array('status', 'label'),
    $data
);