<?php

namespace App\Console\Commands;

use App\Console\Commands\Traits\CanForce;
use App\Models\Endpoint;
use App\Scheduler\Kernel;
use App\Tasks\PingEndpoint;
use GuzzleHttp\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Run extends Command
{
    use CanForce;

    protected $client;

    protected $dispatcher;

    public function __construct(Client $client, EventDispatcher $dispatcher)
    {
        $this->client = $client;
        $this->dispatcher = $dispatcher;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('run');
        $this->addForceOption();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $kernel = new Kernel;

        $endpoints = Endpoint::get();

        foreach ($endpoints as $endpoint) {
            $kernel->add(
                new PingEndpoint($endpoint, $this->client, $this->dispatcher)
            )
            ->everyMinutes(
                $this->isForced($input) ? 1 : $endpoint->frequency
            );
        }

        $kernel->run();
    }
}