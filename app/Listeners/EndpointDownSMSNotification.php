<?php

namespace App\Listeners;

use Symfony\Component\EventDispatcher\Event;
use Twilio\Rest\Client;

class EndpointDownSMSNotification
{
    protected $sms;

    public function __construct(Client $sms)
    {
        $this->sms = $sms;
    }

    public function handle(Event $event)
    {
        $this->sms->messages->create(
            '447720610643',
            [
                'from' => '441398505048',
                'body' => "{$event->endpoint->uri} is DOWN with status code of {$event->endpoint->status->status_code}",
            ]
        );
    }
}
