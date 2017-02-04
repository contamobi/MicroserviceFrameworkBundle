<?php

namespace Cmobi\MicroserviceFrameworkBundle;

use Symfony\Component\Process\Process;

class ProcessManager
{
    protected $list;

    public function __construct()
    {
        $this->list = new \SplObjectStorage();
    }

    public function add(Process $process)
    {
        $this->getProcessList()->attach($process);
    }

    /**
     * @return \SplObjectStorage
     */
    public function getProcessList()
    {
        return $this->list;
    }
}