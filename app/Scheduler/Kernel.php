<?php

namespace App\Scheduler;

use App\Scheduler\Task;

class Kernel
{
    /**
     * The tasks.
     *
     * @var array
     */
    protected $tasks = [];

    /**
     * Add an event.
     *
     * @param Event $event
     */
    public function add(Task $task)
    {
        $this->tasks[] = $task;

        return $task;
    }

    /**
     * Run the scheduled tasks.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->getTasks() as $task) {
            if (!$task->isDueToRun()) {
                continue;
            }

            $task->handle();
        }
    }

    /**
     * Get tasks.
     *
     * @return array
     */
    protected function getTasks()
    {
        return $this->tasks;
    }
}
