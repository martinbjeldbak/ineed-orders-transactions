<?php

/**
 * Class OrderState
 */
class OrderState {
    public static $orderPlaced  = 0;
    public static $paymentReceived = 1;
    public static $inProgress = 2;
    public static $vendorAcknowledgement = 3;
    public static $fulfilled = 4;
    public static $cancelled = 5;

    /**
     * Converts the given order state integer to its corresponding string value
     * @param int $orderState order state integer to translate to string
     * @return string translation of given order state to a string
     */
    public static function toString($orderState) {
        $state = '';

        switch($orderState) {
            case 0:
                $state .= 'Order placed';
                break;
            case 1:
                $state .= 'Payment Received';
                break;
            case 2:
                $state .= 'In Progress';
                break;
            case 3:
                $state .= 'Vendor Acknowledgment';
                break;
            case 4:
                $state .= 'Fulfilled';
                break;
            case 5:
                $state .= 'Cancelled';
        }
        return $state;
    }

    /**
     * Gets the order state for a supplied {Order} ID
     * @param int $id the hexadecimal {Order} ID
     * @return int the state the supplied order is in. See the static variables in this class for what this integer means.
     */
    public static function getOrderStateForOrderId($id) {
        $httpClient = new \GuzzleHttp\Client();

        $res = $httpClient->get("https://ineed-db.mybluemix.net/api/orders/{$id}/order_state");

        return $res->json()['currentState'];
    }

    /**
     * Gets the order state for a supplied order object
     * @param Order $order Order object to get the status for
     * @return int the order state. See the static variables in this class for what this integer means.
     */
    public static function getOrderStateForOrder(Order $order) {
        return self::getOrderStateForOrderId($order->getID());
    }

    public static function setState(Order $order, $state) {
        $httpClient = new \GuzzleHttp\Client();
        $res = $httpClient->post("https://ineed-db.mybluemix.net/api/orders/{$order->getID()}/order_state", [
            'json' => [
                "currentState" => $state
            ]]);
        return $res->json();
    }
}