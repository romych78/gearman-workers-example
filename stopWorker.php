<?php

require_once dirname(__FILE__) . '/abstract.php';

/**
 * script to stop specified OR all workers using different options
 *
 * Class Mage_Shell_Gearman_StopWorker
 */
class Gearman_StopWorker extends General_Abstract
{
    protected $_logEnable = true;
    protected $_registeredWorkers = [
        'asyncsqlqueries',
        'savehistory',
    ];

    protected function usage()
    {
        $this->log('Usage: php shell/gearman/stopWorker.php -j approvesales,asyncsqlqueries  OR use "all" to stop all [ -sl - to stop launchers] [ -fs - create flag in file system instead job ]');
        exit;
    }

    public function run()
    {
        if ($this->getArg('sl') !== false) {
            $slFlag = dirname(__FILE__) . '/stop_all_launchers';
            system('touch ' . $slFlag);
            $this->log('Created flag to stop launchers: ' . $slFlag . ' Please remove it before relaunch.');
        }

        $job = trim($this->getArg('j'));
        if (empty($job)) {
            if ($this->getArg('sl') === false) {
                $this->log('Job name(s) argument is required.');
                $this->usage();
            }
        }

        if ($job == 'all') {
            $job = $this->_registeredWorkers;
        } else {
            $job = explode(',', $job);
        }

        foreach ($job as $j) {
            if (in_array($j, $this->_registeredWorkers)) {
                $jName = 'stop_worker_' . $j;
                if ($this->getArg('fs') !== false) {
                    $fsFlag = dirname(__FILE__) . '/' . $jName;
                    system('touch ' . $fsFlag);
                    $this->log('Flag in file system was created: ' . $fsFlag);
                } else {
                    $queue = Mage::getModel('regiondo_gearman/queue');
                    $task = [];
                    $task['queue'] = $jName;
                    $task['task'] = [];
                    $id = $queue->dispatchTask($task);
                    $this->log("Job '$jName' added to the queue with ID: $id");
                }
            } else {
                $this->log("Job name '$j' not in supported list, please check job name or extend available jobs list.");
            }
        }
    }
}

$shell = new Gearman_StopWorker();
$shell->run();


