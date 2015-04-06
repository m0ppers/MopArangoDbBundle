<?php
namespace Mop\ArangoDbBundle;

interface LoggerInterface
{
    public function log($name, $type, $data);
}
