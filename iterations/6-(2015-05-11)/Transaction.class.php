<?php

class Transaction {
	private $transactionNo;	
        private $state;
	private $productId;
	private $unitPrice;
	private $quantity;
	private $vendor;
	private $mediator;
	private $image;
	private $member;
	
     	public function __construct($transactionNo, $member,$vendor,$productId,$quantity,$mediator,$unitPrice, $image){
		$this->transactionNo = $transactionNo;
		$this -> productId = $productId;		
		$this -> unitPrice = $unitPrice;
		$this -> quantity = $quantity ;
		$this -> vendor = $vendor;
		$this -> member = $member;
		$this -> image = $image;			
		$this->mediator = $mediator;
                $this -> mediator->registerTransaction($this);
        $this -> state = $this -> mediator -> createTransactionState ($this);
	}		
	
		
	public function getVendor()
	{
		return $this->vendor;
	}				 
	public function getTransactionNo ()
	{
		return $this->transactionNo;
	}	 
	public function getState()
	{
		return $this->state;
	}
	public function getProductId()
	{
		return $this->productId;
	}
	public function getUnitPrice()
	{
		return $this->unitPrice;
	}
	public function getImage()
	{
		return $this->image;
	}
	public function setImage($val)
	{	
		$this->image = $val;	
	}
	public function getDeal ()
	{
		return $this->deal;
	}
	public function setDeal ($val)
	{
		$this->deal = $val;
	}
	public function calculateDealDiscount() {
 				
	} 
	
	public function getTotal()
	{
		//need to implement this - this will apply deals and calculate the total price for the given quantity of products	
	}
	public function setQuantity ($val)
	{
		$this->quantity =  $val;
	}	
	public function getQuantity()
	{
		return $this->quantity;
	}	 
}


 
