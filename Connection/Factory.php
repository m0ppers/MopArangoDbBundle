<?php
namespace Mop\ArangoDbBundle\Connection;

use Mop\ArangoDbBundle\LoggerInterface;
use triagens\ArangoDb\Connection;

class Factory
{
    private $loggers = array();

    public function addLogger(LoggerInterface $logger)
    {
        $this->loggers[] = $logger;
    }

    public function createConnection($name, $host, $port)
    {
        $options = array('host' => $host, 'port' => $port);
        if (count($this->loggers)) {
            $loggers = $this->loggers;
            $trace = function($type, $data) use($name, $loggers) {
                foreach ($loggers as $logger) {
                    $logger->log($name, $type, $data);
                }
            };
            $options['trace'] = $trace;
        }
        return new Connection($options);
    }
}
