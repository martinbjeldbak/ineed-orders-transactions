<?php

class OrderTransactionMediator {
	
	private $order;
	private $transactions;
	private $stateMediator;
	private $total;

	public function __construct (){
		$this -> transactions = array (); 
		$this -> stateMediator = new StateMediator ();
	}	
	
	public function registerOrder(Order $order){
		$this -> order = $order; 
	}	

	public function registerTransaction (Transaction $transaction) {
		$this -> transactions [$transaction->getTransactionNo()] = $transaction;	
	}
	
	private function createTransactionState($transaction) {
		$transactionState = new TransactionState (TransactionState :: PLACED, $transaction->getTransactionNo(), $this->stateMediator);
		return $transactionState;				
	}

	private function createOrderState(){
		$orderState = new OrderState(Order::PLACED, $this->order->getOrderNo,$this->stateMediator);
		return $orderState;
	}

	private function calculateTotal() {
		$total = 0;
		foreach ($this -> transactions as $transaction){
			$total += $this->transaction->getUnitPrice () * $this->transaction->getQuantity();
		}
		return $total;
	}						
						
}	
	
