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
	
	public function createTransactionState($transaction) {
		$transactionState = new TransactionState (TransactionState :: PLACED, $transaction->getTransactionNo(), $this->stateMediator);
		return $transactionState;				
	}

	public function createOrderState(){
		$orderState = new OrderState(OrderState::PLACED, $this->order->getOrderNo(),$this->stateMediator);
		return $orderState;
	}

	public function calculateTotal() {
		$total = 0;
		foreach ($this -> transactions as $transaction){
			$total += $transaction->getUnitPrice () * $transaction->getQuantity();
		}
		return $total;
	}						
						
}	
	
