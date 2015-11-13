<?php

require_once dirname(__FILE__) . '/workerAbstract.php';

class Gearman_SaveHistory extends Gearman_WorkerAbstract
{

    const JOB_SAVE_TICKET_HISTORY = 'save_ticket_history';

    /**
     * Each workr have to return unique task name which could be proceed by this worker
     * better to keep one worker per task (at least in this example)
     *
     * @return string
     */
    protected function getJobName()
    {
        return self::JOB_SAVE_TICKET_HISTORY;
    }

    protected function processData($data)
    {
        if (!empty($data['important_required_data'])) {
            /**
             * @TODO add here your logic, doing something with $data etc. Below just an example
             */
        }
        $this->checkIfShouldStopWork();
    }

}

$shell = new Gearman_SaveHistory();
$shell->run();


