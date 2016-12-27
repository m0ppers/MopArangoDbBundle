<?php
namespace Mop\ArangoDbBundle\Connection;

use Mop\ArangoDbBundle\LoggerInterface;
use triagens\ArangoDb\ConnectionOptions;
use triagens\ArangoDb\Connection;

class Factory
{
    private $loggers = array();

    public function addLogger(LoggerInterface $logger)
    {
        $this->loggers[] = $logger;
    }

    public function createConnection(
        $name,
        $host,
        $port = 8529,
        $databaseName = '_system',
        $user = 'root',
        $password = ''
    ) {
        $options = array(
            ConnectionOptions::OPTION_ENDPOINT => sprintf('tcp://%s:%d', $host, $port),
            ConnectionOptions::OPTION_DATABASE => $databaseName,
            ConnectionOptions::OPTION_AUTH_TYPE => 'Basic',
            ConnectionOptions::OPTION_AUTH_USER => $user,
            ConnectionOptions::OPTION_AUTH_PASSWD => $password,
        );

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
