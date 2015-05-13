<?php

class Orders {
	private int $orderNo;	
	private $orderState;
	private $member;
	private $tax;
	private $deal;
	private $vendor;
	private $mediator;
	private $vendorLink;	
	private $vendorImage;
	private $pickUpLocation ;
	private $orderPlacedDate;
	private $orderFulfilledDate;
	
     	public function __construct($orderNo,$member,$vendor, $mediator,$vendorLink,$pickUpLocationi,$orderPlacedDate,$deal){
		$this->orderNo = $orderNo;	
		$this->mediator = $mediator;
		$this->mediator->registerOrder($this);
		$this->orderState = $this->mediator->createOrderState();
		$this-> total = $this->mediator -> calculateTotal();
		$this->member = $member;
		$this->vendor = $vendor;
		$this->vendorLink = $vendorLink;  
		$this->vendorImage = $vendorImage;
		$this->pickUpLocation = $pickUpLocation;
		$this->orderPlacedDate = $orderPlacedDate;
		$this->orderFulfilledDate = $orderFulfilledDate;
		$this->deal = $deal;
	}				 
	public function getOrderNo ()
	{
		return $orderNo;
	}	 
	public function getOrderState()
	{
		return $orderState;
	}
	public function getTotal()
	{
		return $this->mediator->calculateTotal();
	}
	public function getMember()
	{
		return $member;
	}
	public function getTax()
	{
		return $tax;
	}
	public function getDealsDiscount()
	{	
		/*need to write code to use the $deal object to find out the deal discount*/
		return 0;
	}
	public function getDeal ()
	{
		return $deal;
	}
	public function getVendor ()
	{
		return $vendor;
	}
	public function getMediator()
	{
		return $mediator;
	}
	public function getVendorLink()
	{
		return $vendorLink;
	}
	public function getVendorImage()
	{
		return $vendorImage;
	}
	public function getPickUpLocation()
	{
		return $pickUpLocation;
	}
	public function getOrderPlacedDate()
	{	
		return $orderPlacedDate;
	}
	public function getOrderFulfilledDate()
	{
		return $orderFulfilledDate;
	}
	
	public function calculateCumulativeCost()
	{
		//need to implement this - this will apply deals and calculate the total price for the given quantity of products	
	}
	public function setOrderNo($value)
	{
		$orderNo = $value;
	}
	public function setOrderState($value)
	{
		$orderState = $value;
	} 
	public function setTotal($value)
	{
		$total=$value;
	}
	public function setMember ($value)
	{
		$member= $value;	
	}
	public function setTax($value)
	{
		$tax = $value;
	}
	public function setDeal($value)
	{
		$deal = $value;
	}
	public function setVendor ($value) 
	{
		$vendor = $value;
	}
	public function setMediator ($value)
	{
		$mediator = $value;
	}
	public function setVendorLink ($value)
	{
		$vendorLink = $value;
	}
	public function setVendorImage ($value)
	{
		$vendorImage = $value;
	}
	public function setPickUpLocation ($value)
	{
		$pickUpLocation = $value;
	}
	public function setOrderPlacedDate ($value)
	{
		$orderPlacedDate = $value;
	}
	public function setOrderFulfilledDate ($value)
	{
		$orderFulfilledDate = $value;
	}

}


 
