<?php

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
/*
foreach ($statuses as $code => $info) {
    $data[] = array(
        'status' => $code,
        'label'  => $info['label']
    );
}
*/
$installer->getConnection()->insertArray(
    $installer->getTable('sales/order_status'),
    array('status', 'label'),
    $data
);