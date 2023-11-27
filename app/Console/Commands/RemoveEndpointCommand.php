<?php

namespace App\Console\Commands;

use App\Models\Endpoint;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RemoveEndpointCommand extends Command
{
    protected function configure()
    {
        $this->setName('endpoint:remove')
            ->addArgument('id', InputArgument::REQUIRED, 'The endpoint ID to remove');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $endpoint = Endpoint::find($id = $input->getArgument('id'));

        if (!$endpoint) {
            $output->writeln("<error>Endpoint with ID {$id} does not exist.</error>");
        }

        $endpoint->delete();

        $output->writeln("<info>Endpoint ID {$id} has been deleted.</info>");
    }  
}