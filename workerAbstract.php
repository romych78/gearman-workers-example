<?php


abstract class Gearman_WorkerAbstract
{
    /*
     * filename in working directory
     * which, if exists, tell to all workers exit after next round
     */
    const FNAME_FLAG_STOP_ALL = 'stop_all_workers';

    /**
     * max memory MB to work, otherwise stop
     */
    const MEMORY_LIMIT = 1000;

    /**
     * Filename as a flag when we want kill only specific/individual worker
     * @var bool | string
     */
    protected $_fnameFlagStop = false;

    /**
     * flag to enable/disable debug output
     * @var bool
     */
    protected $_logEnable = true;

    /** @var $_curJobObject GearmanJob */
    protected $_curJobObject = null;

    abstract protected function processData($data);

    abstract protected function getJobName();

    /**
     * mysql and redis coud disconnect
     * then worker gets error like Read connection error, or MySQL gone away etc
     * to fix this issue we try first read something from Cache twice - first in try/catch and then without
     * first error ignored, second will catched on next level and script will be terminated
     *
     * For DB twice read doesn't work -- on lost connection we have to close connection and then open it again
     * only in this case connection works again
     *
     * NOTE: I leave this method as is (it used in Magento based project), because this part could be a good example for Magento projects
     */
    protected function testConnections()
    {
        $cacheId = 'store_1_example_config_cache';
        try {
            $cacheData = Mage::app()->loadCache($cacheId);
        } catch (Exception $e) {
        }

        try {
            $dbData = $this->getConn()->fetchOne('SELECT NOW()');
        } catch (Exception $e) {
            $db = Mage::getSingleton('core/resource')->getConnection('core_read');
            $db->closeConnection();
            $db->getConnection();
            $this->getConn()->query("SET NAMES 'utf8' COLLATE 'utf8_unicode_ci'");
        }

        $cacheData = Mage::app()->loadCache($cacheId);
        $dbData = $this->getConn()->fetchOne('SELECT NOW()');
    }

    /**
     * NOTE
     *
     * @param $data array
     * @param $job GearmanJob
     */
    protected function processDataWrapper($data, $job)
    {
        try {
            $this->testConnections();
            $this->processData($data);
        } catch (Exception $e) {
            // @TODO probably we could do something with $job model (but any return to gearman will remove this task from queue)
            $this->log($e->getMessage());
            exit;
        }
    }

    /**
     * NOTE about $this->_fnameFlagStop:
     *      stop_worker_ + in lower case last part of class name ( for Mage_Shell_Gearman_ApproveSales will be
     * approvesales) Ex.: for Gearman_SaveHistory == stop_worker_asyncsqlqueries we will subscrabe for same
     * job name
     *
     * @throws Exception
     */
    public function run()
    {
        $this->_fnameFlagStop = dirname(__FILE__) . '/' . $this->getIndividualFilenameAsFlag();
        $this->checkIfShouldStopWork();

        $worker = Mage::getModel('myproject_gearman/worker'); // just a wrapper around PHP Gearman class

        $worker->addFunction($this->getJobName(), function (GearmanJob $job) {
            $this->_curJobObject = $job;
            $data = unserialize($job->workload());
            $this->processDataWrapper($data, $job);
        });

        $worker->addFunction($this->getIndividualFilenameAsFlag(), function (GearmanJob $job) {
            $this->log('Got job to exit. Job name: ' . $this->getIndividualFilenameAsFlag());
            $job->sendComplete('ok'); // without this line queue not cleans and task stay in this queue
            exit;
        });

        $worker->work();
    }

    protected function getIndividualFilenameAsFlag()
    {
        return 'stop_worker_' . strtolower(substr(get_class($this), strrpos(get_class($this), '_') + 1));
    }

    protected function checkIfShouldStopWork()
    {
        $fname = dirname(__FILE__) . '/' . self::FNAME_FLAG_STOP_ALL;
        $usedMemory = round(memory_get_usage() / 1024 / 1024, 1);

        if (file_exists($fname)) {
            $this->log('Method checkIfShouldStopWork() found that \'' . $fname . '\' exists. Exiting from \'' . $this->getJobName() . '\' worker.');
            exit;
        } else {
            if (file_exists($this->_fnameFlagStop)) {
                $this->log('Method checkIfShouldStopWork() found that \'' . $this->_fnameFlagStop . '\' exists. Exiting from \'' . $this->getJobName() . '\' worker.');
                exit;
            } else {
                if ($usedMemory > self::MEMORY_LIMIT) {
                    $this->log('Method checkIfShouldStopWork() found that worker used too much memory. Exiting from \'' . $this->getJobName() . '\' worker.');
                    exit;
                }
            }
        }


    }

}



