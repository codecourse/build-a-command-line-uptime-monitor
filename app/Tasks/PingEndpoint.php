<?php

namespace App\Tasks;

use App\Events\EndpointIsDown;
use App\Events\EndpointIsUp;
use App\Models\Endpoint;
use App\Scheduler\Task;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\EventDispatcher\EventDispatcher;

class PingEndpoint extends Task
{
    protected $endpoint;

    protected $client;

    protected $dispatcher;

    public function __construct(Endpoint $endpoint, Client $client, EventDispatcher $dispatcher)
    {
        $this->endpoint = $endpoint;
        $this->client = $client;
        $this->dispatcher = $dispatcher;
    }

    public function handle()
    {
        try {
            $response = $this->client->request('GET', $this->endpoint->uri);
        } catch (RequestException $e) {
            $response = $e->getResponse();
        }

        $this->endpoint->statuses()->create([
            'status_code' => $response->getStatusCode()
        ]);

        $this->dispatchEvents();
    }

    protected function dispatchEvents()
    {
        if ($this->endpoint->status->isDown()) {
            $this->dispatcher->dispatch(EndpointIsDown::NAME, new EndpointIsDown($this->endpoint));
        }

        if ($this->endpoint->isBackUp()) {
            $this->dispatcher->dispatch(EndpointIsUp::NAME, new EndpointIsUp($this->endpoint));
        }
    }
}