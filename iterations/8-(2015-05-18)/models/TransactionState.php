<?php

class TransactionState {
    public static $orderPlaced  = 0;
    public static $paymentReceived = 1;
    public static $inProgress = 2;
    public static $vendorAcknowledgement = 3;
    public static $fulfilled = 4;
    public static $cancelled = 5;

    public static function toString($transactionState) {
        $state = '';

        switch($transactionState) {
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

    public static function getTransStateForTransId($id) {
        $httpClient = new \GuzzleHttp\Client();

        $res = $httpClient->get("https://ineed-db.mybluemix.net/api/transactions/{$id}/transaction_state");
        return $res->json()['currentState'];
    }

    public static function getTransStateForTrans(Transaction $trans) {
        return self::getTransStateForTransId($trans->id);
    }

    public static function setState(Transaction $trans, $state) {
        $httpClient = new \GuzzleHttp\Client();
        $res = $httpClient->post("https://ineed-db.mybluemix.net/api/transactions/{$trans->id}/transaction_state", [
            'json' => [
                "currentState" => $state
            ]]);
        return $res->json();
    }
}