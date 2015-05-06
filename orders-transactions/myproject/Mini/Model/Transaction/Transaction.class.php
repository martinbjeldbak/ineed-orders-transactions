<?php

class Transaction {
	private $transactionNo;	
      	private $state;
	private $productId;
	private $unitPrice;
	private $quantity;
	private $store;
	private $mediator;
	private $deal;	
	private $image;
      
	private getTransactionNo ()
	{
		return $transactionNo;
	}	 
	private getState()
	{
		return $state;
	}
	private getProductId()
	{
		return $productId;
	}
	private getUnitPrice()
	{
		return $unitPrice;
	}
	private getImage()
	{
		return $image;
	}
	private setImage($val)
	{	
		$image = $val;	
	}
	private getDeal ()
	{
		return $deal;
	}
	private setDeal ($val)
	{
		$deal = $val;
	}
	private calculateDealDiscount() {
	} 
	
	private getTotal()
	{
		//need to implement this - this will apply deals and calculate the total price for the given quantity of products	
	}
   
}


 
