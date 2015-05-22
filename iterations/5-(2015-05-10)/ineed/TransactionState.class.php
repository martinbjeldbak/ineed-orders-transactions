<?php

class TransactionState{

	const PLACED = 0;
	const FULFILLED = 1;
	const CANCELLED = 2;
	private $currentState;
	private $transactionNo;
	private $stateMediator;
	public function __construct ($state,$transactionNo,StateMediator $mediator){
		$this->currentState = $state;
		$this->stateMediator = $mediator;
		$this->stateMediator->registerTransactionState ($this);
		$this->transactionNo = $transactionNo;	
	}
	public function getState () 
	{
		return $this->currentState;
	}
	public function getTransactionNo () 
	{
		return $this->transactionNo;
	}
	public function setState($value)
	{
		$this->currentState = $value;
		$this->stateMediator->transactionUpdate();	
	}

}
