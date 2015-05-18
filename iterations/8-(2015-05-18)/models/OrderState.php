<?php

class OrderState {
    public static $orderPlaced  = 0;
    public static $paymentReceived = 1;
    public static $inProgress = 2;
    public static $vendorAcknowledgement = 3;
    public static $fulfilled = 4;
    public static $cancelled = 5;

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
}