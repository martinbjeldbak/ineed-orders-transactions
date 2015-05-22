<?php
class TransactionState {
  	private $vendorAcknowledge; 
   	private $vendorReady;
	private $fulfilled;
	private $cancelled;

	public function setVendorAcknowledge($val) {
       		$vendorAcknowledge = $val;
	}
	public function setVendorReady($val) {
                $vendorReady = $val;
        } 	
	public function setFulfilled($val) {
                $fulfilled = $val;
        } 
	public function setCancelled($val) {
                $cancelled = $val;
        } 
	public function getVendorAcknowledge($val){
		return $vendorAcknowledge;	
	}	
	public function getVendorReady($val){ 
                return $vendorReady;
        }
	public function getFulfilled($val){ 
                return $fulfilled;
        }
	public function getCancelled($val) {
		return $cancelled;
	}
	
} 
