<?php
namespace Mop\AvocadoBundle;

interface LoggerInterface
{
    public function log($name, $type, $data);
}
