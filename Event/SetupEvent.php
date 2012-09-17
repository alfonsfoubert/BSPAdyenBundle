<?php

namespace BSP\AdyenBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class SetupEvent extends Event
{
    protected $parameters;

    public function __construct( $parameters )
    {
        $this->parameters = $parameters;
    }

    public function getParameters()
    {
        return $this->parameters;
    }
}
