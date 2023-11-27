<?php

namespace App\Events;

use App\Models\Endpoint;
use Symfony\Component\EventDispatcher\Event;

class EndpointIsUp extends Event
{
    const NAME = 'endpoint.up';

    public $endpoint;

    public function __construct(Endpoint $endpoint)
    {
        $this->endpoint = $endpoint;
    }
}
