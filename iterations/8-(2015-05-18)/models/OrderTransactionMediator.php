<?php

require_once __DIR__.'/Order.php';
require_once __DIR__.'/Transaction.php';

class OrderTransactionMediator {
    /* @var $order Order */
	private $order;
    /* @var $transactions Transaction[] */
	private $transactions;

	public function __construct(){
            $this->transactions = array(); 
	}	
	
	public function registerOrder(Order $order){
            $this->order = $order; 
	}	

	public function registerTransaction(Transaction $transaction) {
            array_push($this->transactions, $transaction);
	}

    // Support deleting of a transaction from order
    public function unregisterTransaction(Transaction $transaction) {
        if(($key = array_search($transaction, $this->transactions)) !== FALSE) {
            unset($this->transactions[$key]);
        }
    }

    // Order -> Transactions
    public function createTransactionsInDB() {
        foreach ($this->transactions as $transaction){
            $transaction->createInDB();
        }
    }

    // Transaction -> Order
	public function updateTotal() {
            $total = 0;
            foreach ($this->transactions as $transaction) {
                    $total += $transaction->getUnitPrice() * $transaction->getQuantity();
            }
            $this->order->total = $total;
            return $total;
	}

    // Transaction -> Order -- order state not guaranteed to change
    public function updateOrderState() {
        //check minimum state of all transactions, and set it in order state
        $minState = NULL;
        foreach ($this->transactions as $transaction){
            if (!$minState) {
                $minState = $transaction->getTransactionState();
            }
            else if ($transaction->transactionState < $minState) {
                $minState = $transaction->transactionState;
            }
        }
        if ($this->order->orderState != $minState) {
            $this->order->updateOrderState($minState);
        }
    }
						
}	
	
