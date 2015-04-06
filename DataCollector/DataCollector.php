<?php
namespace Mop\ArangoDbBundle\DataCollector;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector as BaseDataCollector;

class DataCollector extends BaseDataCollector implements LoggerInterface
{
    /**
     * @var array
     */
    private $interactions = array();

    /**
     * @var string
     */
    private $lastType = null;

    /**
     * @param string $name
     * @param string $type
     * @param mixed $data
     */
    public function log($name, $type, $data)
    {
        if (isset($this->lastType)) {
            // mop: not sure if this can happen?
            if ($this->lastType == $type) {
                throw new \UnexpectedValueException(
                    'Last type was ' . $this->lastType . ' and current type is ' . $type . '. WTF?'
                );
            }
        }
        $this->lastType = $type;
        if ($type == 'send') {
            // mop: hmm...no Request::fromString :(
            if (!preg_match("#^([A-Z]+) ([^ ]+) HTTP/1.(0|1).*\r\n\r\n(.*)$#sm", $data, $matches)) {
                throw new \UnexpectedValueException(
                    'Couldn\'t parse request ' . strtr($data, array("\r" => 'X', "\n" => 'Y'))
                );
            }
            $this->interactions[] = array(
                'connection' => $name,
                'requestMethod' => $matches[1],
                'uri' => $matches[2],
                'data' => $matches[4],
                'start_time' => microtime(true)
            );
        } else {
            // mop: hmm...no Response::fromString :(
            if (!preg_match("#^HTTP/1.(0|1) (\d+).*Content-Length: (\d+)#ims", $data, $matches)) {
                throw new \UnexpectedValueException('Couldn\'t parse response ' . $data);
            }
            $lastIndex = count($this->interactions) - 1;
            $this->interactions[$lastIndex]['time'] = microtime(true) - $this->interactions[$lastIndex]['start_time'];
            $this->interactions[$lastIndex]['code'] = $matches[2];
            $this->interactions[$lastIndex]['responseLength'] = $matches[3];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $this->data = array(
            'interactions' => $this->interactions,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'arangodb';
    }

    /**
     * @return mixed
     */
    public function getInteractions()
    {
        return $this->data['interactions'];
    }

    /**
     * @return bool
     */
    public function hasInteractions()
    {
        return !empty($this->data['interactions']);
    }

    /**
     * @return int
     */
    public function getTotalTime()
    {
        $total = 0;
        foreach ($this->data['interactions'] as $interaction) {
            $total += $interaction['time'];
        }
        return $total;
    }

    /**
     * @return int
     */
    public function getInteractionsCount()
    {
        return count($this->data['interactions']);
    }
}
