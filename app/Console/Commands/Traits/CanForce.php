<?php

namespace App\Console\Commands\Traits;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

trait CanForce
{
    public function addForceOption()
    {
        $this->addOption(
            'force',
            'f',
            InputOption::VALUE_OPTIONAL,
            'Force check regardless of frequency',
            false
        );
    }

    protected function isForced(InputInterface $input)
    {
        return $input->getOption('force') !== false;
    }
}