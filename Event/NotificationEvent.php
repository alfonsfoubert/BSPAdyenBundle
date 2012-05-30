<?php

namespace BSP\AdyenBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class NotificationEvent extends Event
{
	protected $live;
	protected $eventCode;
	protected $pspReference;
	protected $originalReference;
	protected $merchantReference;
	protected $merchantAccountCode;
	protected $eventDate;
	protected $success;
	protected $paymentMethod;
	protected $operations;
	protected $reason;
	protected $amount;
	protected $rawData;

	protected function __construct(	$live, $eventCode, $pspReference, $originalReference, 
									$merchantReference, $merchantAccountCode, $eventDate, 
									$success, $paymentMethod, $operations, $rawData,
									$reason, $amount )
	{
		$this->live = $live;
		$this->eventCode = $eventCode;
		$this->pspReference = $pspReference;
		$this->originalReference = $originalReference;
		$this->merchantReference = $merchantReference;
		$this->merchantAccountCode = $merchantAccountCode;
		$this->eventDate = $eventDate;
		$this->success = $success;
		$this->paymentMethod = $paymentMethod;
		$this->operations = $operations;
		$this->reason = $reason;
		$this->amount = $amount;
		$this->rawData = $rawData;
	}
	
	public function isLive() {
		return $this->live;
	}

	public function getEventCode() {
		return $this->eventCode;
	}

	public function getPspReference() {
		return $this->pspReference;
	}

	public function getOriginalReference() {
		return $this->originalReference;
	}

	public function getMerchantReference() {
		return $this->merchantReference;
	}

	public function getMerchantAccountCode() {
		return $this->merchantAccountCode;
	}

	public function getEventDate() {
		return $this->eventDate;
	}

	public function isSuccess() {
		return $this->success;
	}

	public function getPaymentMethod() {
		return $this->paymentMethod;
	}

	public function getOperations() {
		return $this->operations;
	}

	public function getRawData() {
		return $this->rawData;
	}
	
	public function getReason() {
		return $this->reason;
	}

	public function getAmount() {
		return $this->amount;
	}
}
