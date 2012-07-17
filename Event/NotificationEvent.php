<?php

namespace BSP\AdyenBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class NotificationEvent extends Event
{
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
    protected $aditionalData;
    protected $authResult;

    public function __construct( $item )
    {
        $this->rawData = $item;
        $this->eventCode = $item->eventCode;
        $this->pspReference = $item->pspReference;
        $this->originalReference = $item->originalReference;
        $this->merchantReference = $item->merchantReference;
        $this->merchantAccountCode = $item->merchantAccountCode;
        $this->eventDate = $item->eventDate;
        $this->success = $item->success;
        $this->paymentMethod = $item->paymentMethod;
        $this->operations = $item->operations;
        $this->reason = $item->reason;
        $this->amount = $item->amount;
        $this->additionalData = $item->additionalData;
        $this->authResult = $item->eventCode ?: $item->authResult;
    }

    public function getEventCode()
    {
        return $this->eventCode;
    }

    public function getPspReference()
    {
        return $this->pspReference;
    }

    public function getOriginalReference()
    {
        return $this->originalReference;
    }

    public function getMerchantReference()
    {
        return $this->merchantReference;
    }

    public function getMerchantAccountCode()
    {
        return $this->merchantAccountCode;
    }

    public function getEventDate()
    {
        return $this->eventDate;
    }

    public function isSuccess()
    {
        return $this->success;
    }

    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    public function getOperations()
    {
        return $this->operations;
    }

    public function getRawData()
    {
        return $this->rawData;
    }

    public function getReason()
    {
        return $this->reason;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function getAdditionalData()
    {
        return $this->additionalData;
    }

    public function getAuthResult()
    {
        return $this->authResult;
    }
}
