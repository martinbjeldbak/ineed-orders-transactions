<?php

class Order {
	private $orderNo;	
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
	
     	public function __construct($member,$vendor, $mediator,$vendorLink,$pickUpLocation,$orderPlacedDate,$deal,$vendorImage){
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
		$this->deal = $deal;
	}				 
	public function getOrderNo ()
	{
		return $this->orderNo;
	}	 
	public function getOrderState()
	{
		return $this->orderState;
	}
	public function getTotal()
	{
		return $this->mediator->calculateTotal();
	}
	public function getMember()
	{
		return $this->member;
	}
	public function getTax()
	{
		return 0;
	}
	public function getDealsDiscount()
	{	
		/*need to write code to use the $deal object to find out the deal discount*/
		return 0;
	}
	public function getDeal ()
	{
		return $this->deal;
	}
	public function getVendor ()
	{
		return $this->vendor;
	}
	public function getMediator()
	{
		return $this->mediator;
	}
	public function getVendorLink()
	{
		return $this->vendorLink;
	}
	public function getVendorImage()
	{
		return $this->vendorImage;
	}
	public function getPickUpLocation()
	{
		return $this->pickUpLocation;
	}
	public function getOrderPlacedDate()
	{	
		return $this->orderPlacedDate;
	}
	public function getOrderFulfilledDate()
	{
		return $this->orderFulfilledDate;
	}
	
	public function calculateCumulativeCost()
	{
		//need to implement this - this will apply deals and calculate the total price for the given quantity of products	
	}
	public function setOrderNo($value)
	{
		$this->orderNo = $value;
	}
	public function setOrderState($value)
	{
        $this->orderState = $value;
	} 
	public function setTotal($value)
	{
        $this->total=$value;
	}
	public function setMember ($value)
	{
        $this->member= $value;
	}
	public function setTax($value)
	{
        $this->tax = $value;
	}
	public function setDeal($value)
	{
        $this->deal = $value;
	}
	public function setVendor ($value) 
	{
        $this->vendor = $value;
	}
	public function setMediator ($value)
	{
        $this->mediator = $value;
	}
	public function setVendorLink ($value)
	{
        $this->vendorLink = $value;
	}
	public function setVendorImage ($value)
	{
        $this->vendorImage = $value;
	}
	public function setPickUpLocation ($value)
	{
        $this->pickUpLocation = $value;
	}
	public function setOrderPlacedDate ($value)
	{
        $this->orderPlacedDate = $value;
	}
	public function setOrderFulfilledDate ($value)
	{
        $this->orderFulfilledDate = $value;
	}
}


 
