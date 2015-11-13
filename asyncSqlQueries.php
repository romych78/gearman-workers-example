<?php

require_once dirname(__FILE__) . '/workerAbstract.php';

/**
 * Sample calss, for example if you need some SQL have to be executed by worker
 * specially if we have lots of queries and we have to proceed them postpone in queue
 *
 * Class Gearman_asyncSqlQueries
 */
class Gearman_asyncSqlQueries extends Gearman_WorkerAbstract
{
    const JOB_ASYNC_SQL_QUERIES = 'async_sql_queries';

    /**
     * Each workr have to return unique task name which could be proceed by this worker
     * better to keep one worker per task (at least in this example)
     * good to use some constant, which
     *
     * @return string
     */
    protected function getJobName()
    {
        return self::JOB_ASYNC_SQL_QUERIES;
    }

    protected function processData($data)
    {
        /**
         * @TODO add here your logic, doing something with $data etc. Below just an example
         */
        if (!empty($data['queries_to_execute']) && is_array($data['queries_to_execute'])) {
            foreach ($data['queries_to_execute'] as $sql) {
                try {
                    $this->getConn()->exec($sql);
                } catch (Exception $e) {
                    $this->log('ERROR: ' . $e->getMessage());
                }
            }
        }

        /**
         * then we call method which checks and, if needed, exit this worker
         */
        $this->checkIfShouldStopWork();
    }

}

$shell = new Gearman_asyncSqlQueries();
$shell->run();


