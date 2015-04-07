<?php
namespace Mop\ArangoDbBundle\Connection;

use Mop\ArangoDbBundle\DataCollector\LoggerInterface;
use triagens\ArangoDb\Connection;

/**
 * @package Mop\ArangoDbBundle\Connection
 */
class Factory
{
    /**
     *
     * @var LoggerInterface[]
     */
    private $loggers = array();

    /**
     * @param LoggerInterface $logger
     */
    public function addLogger(LoggerInterface $logger)
    {
        $this->loggers[] = $logger;
    }

    /**
     * @param string $name
     * @param string $host
     * @param integer $port
     *
     * @return Connection
     */
    public function createConnection($name, $host, $port)
    {
        $options = array(
            'host' => $host,
            'port' => $port
        );

        if (count($this->loggers) > 0) {
            $loggers = $this->loggers;
            $trace = function ($type, $data) use ($name, $loggers) {
                foreach ($loggers as $logger) {
                    $logger->log($name, $type, $data);
                }
            };
            $options['trace'] = $trace;
        }
        return new Connection($options);
    }
}
