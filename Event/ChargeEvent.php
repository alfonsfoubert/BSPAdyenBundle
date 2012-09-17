<?php

namespace BSP\AdyenBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class ChargeEvent extends Event
{
    protected $transaction;
    protected $account;
    protected $email;
    protected $amount;
    protected $currency;
    protected $success = false;

    public function __construct( $transaction, $account, $email, $amount, $currency, $success )
    {
        $this->transaction = $transaction;
        $this->account = $account;
        $this->email = $email;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->success = (bool) $success;
    }

    public function getTransaction()
    {
        return $this->transaction;
    }

    public function getAccount()
    {
        return $this->account;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function getCurrency()
    {
        return $this->currency;
    }

    public function isSucces()
    {
        return $this->success;
    }
}
