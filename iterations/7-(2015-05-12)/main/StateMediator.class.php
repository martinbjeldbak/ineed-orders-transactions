<?php
require_once ("OrderState.class.php");
require_once ("TransactionState.class.php");
class StateMediator{

	private $orderState;
	private $transactionStates;
	
	public function __construct (){
		$this->transactionStates = array();
	}
	public function registerOrderState (OrderState $orderState)
	{
		$this -> orderState = $orderState; 	
	}
	public function registerTransactionState (TransactionState $transactionState)
	{
		$this -> transactionStates[$transactionState->getTransactionNo()] = $transactionState;	
	}
	public function transactionUpdate ()
	{
		$isOrderComplete = true;
		$isOrderCancelled = false;
		foreach ($transactionStates as $transactionState)
		{
			$isOrderComplete = $isOrderComplete && ($transactionState->currentState == TransactionState :: FULFILLED) ;
			$isOrderCancelled = $isOrderCancelled && ($transactionState -> currentState == TransactionState :: CANCELLED) ;
		}
		if($isOrderComplete){
			$this -> orderState -> setState(OrderState :: FULFILLED ) ;	
		}
		if($isOrderCancelled){
			$this -> orderState -> setState (OrderState :: CANCELLED);
		}
		
	}	

}
