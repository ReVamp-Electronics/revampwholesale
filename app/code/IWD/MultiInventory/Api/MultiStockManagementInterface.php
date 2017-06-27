<?php

namespace IWD\MultiInventory\Api;

/**
 * Interface MultiStockManagementInterface
 * @api
 */
interface MultiStockManagementInterface
{
    /**
     * Load order
     *
     * @param int $orderId
     * @return $this
     */
    public function loadOrder($orderId);

    /**
     * Get qty ordered for order
     *
     * @return float
     */
    public function getOrderQtyOrdered();

    /**
     * Get qty assigned for order
     *
     * @return float
     */
    public function getOrderQtyAssigned();

    /**
     * Get is order assigned to stock
     *
     * @return bool
     */
    public function getIsOrderAssignedToStock();

    /**
     * Get is order placed before init multi stock inventory
     *
     * @return bool
     */
    public function getIsOrderPlacedBeforeInit();

    /**
     * Get stocks list
     *
     * @return []
     */
    public function getStocksList();

    /**
     * Get order items
     *
     * @return []
     */
    public function getOrderItems();
}
