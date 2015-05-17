<?php

class OrderState {
    public static $orderPlaced  = 0;
    public static $paymentReceived = 1;
    public static $inProgress = 2;
    public static $vendorAcknowledgement = 3;
    public static $fulfilled = 4;
    public static $cancelled = 5;
}