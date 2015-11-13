<?php

abstract class General_Abstract
{
    protected $_logEnable = false;
    protected $_conn = null;


    /**
     * Input arguments
     * @var array
     */
    protected $_args = [];

    /**
     * Initialize application and parse input parameters
     *
     */
    public function __construct()
    {
        $this->_parseArgs();
    }

    /**
     * Parse input arguments
     *
     * @return Mage_Shell_Abstract
     */
    protected function _parseArgs()
    {
        $current = null;
        foreach ($_SERVER['argv'] as $arg) {
            $match = [];
            if (preg_match('#^--([\w\d_-]{1,})$#', $arg, $match) || preg_match('#^-([\w\d_]{1,})$#', $arg, $match)) {
                $current = $match[1];
                $this->_args[ $current ] = true;
            } else {
                if ($current) {
                    $this->_args[ $current ] = $arg;
                } else {
                    if (preg_match('#^([\w\d_]{1,})$#', $arg, $match)) {
                        $this->_args[ $match[1] ] = true;
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Run script
     *
     */
    abstract public function run();

    /**
     * @return Zend_Db_Adapter_Pdo_Mysql
     */
    protected function getConn()
    {
        /**
         * @TODO implement get connection here
         */

        return $this->_conn;
    }

    public function log($msg)
    {
        if ($this->_logEnable) {
            echo "\n";
            if (is_string($msg)) {
                echo $msg;
            } else {
                print_r($msg);
            }
        }
    }


    /**
     * Retrieve argument value by name or false
     *
     * @param string $name the argument name
     *
     * @return mixed
     */
    public function getArg($name)
    {
        if (isset($this->_args[ $name ])) {
            return $this->_args[ $name ];
        }

        return false;
    }

}
