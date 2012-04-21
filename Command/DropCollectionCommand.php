<?php

namespace Mop\AvocadoBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use triagens\Avocado\CollectionHandler;
use triagens\Avocado\Collection;

class DropCollectionCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('avocado:drop-collection')
            ->setDefinition(array(
                new InputArgument('collectionName' , InputArgument::REQUIRED, 'Collection name'),
            ))
            ->setDescription('Drops an Avocado Collection');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $connection = $this->getContainer()->get('mop_avocado.default_connection');
        $handler = new CollectionHandler($connection);
        
        $collectionName = $input->getArgument('collectionName');
        $handler->delete($collectionName);
        $output->writeLn('Collection '.$collectionName.' has been dropped');
    }
}
