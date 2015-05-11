<?php

class OrderState{

	const PLACED = 0;
	const FULFILLED = 1;
	const CANCELLED = 2;
	private $currentState;
	private $orderNo;
	private $stateMediator;
	public function __construct ($state,$orderNo,StateMediator $stateMediator){
		$this -> currentState = $state;
		$this -> orderNo = $orderNo;
		$this-> stateMediator = $stateMediator;
		$this -> stateMediator -> registerOrderState($this);
	}
	public function getState () 
	{
		return $this -> currentState;
	}
	public function setState($value)
	{
		$this -> currentState = $value;
	}
	
}
