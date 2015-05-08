<?php namespace iNeed;

use GuzzleHttp\Message\ResponseInterface;

require __DIR__ . '/../vendor/autoload.php';
include __DIR__ . '/../utils/Team10DB.php';

class Transaction implements iDBModel {
    private $order, $itemID, $quantity, $unitPrice, $vendorID, $dealID, $dealDiscount;
    private $committed = false;

    /**
     *
     *
     * param $order Order The order object that belongs to this transaction
     *
     * @param $orderID String Object id of the order
     * @param $itemID String Object id of the itme
     * @param $quantity Integer how many of this item are part of the transactino
     * @param $unitPrice Integer The price of each
     * @param $vendorID String ID of vendor
     * @param $dealID String ID of deal
     * @param $dealDiscount Float discount
     */
    function __construct($orderID, $itemID, $quantity, $unitPrice, $vendorID, $dealID, $dealDiscount) {
        // TODO: Isn't $unitPrice, $dealDiscount part of the order? E.g. it's just $order->total?
        $this->orderID = $orderID;
        $this->itemID = $itemID;
        $this->quantity = $quantity;
        $this->unitPrice = $unitPrice;
        $this->vendorID = $vendorID;
        $this->dealID = $dealID;
        $this->dealDiscount = $dealDiscount;
    }


    /**
     * Commits this object to Team 10's database.
     *
     * Returns a GuzzleHttp ResponseInterface that has information about the POST request.
     * @return ResponseInterface object
     */
    function commit() {
        if($this->committed == true)
            return -1;

        $res = Team10DB::post('transactions', [
            // TODO: Put params here
        ]);

        $this->committed = true;

        return $res;
    }

    public function hasCommitted()
    {
        return $this->committed;
    }
}