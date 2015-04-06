<?php
namespace Mop\ArangoDbBundle\DataCollector;

interface LoggerInterface
{
    public function log($name, $type, $data);
}
