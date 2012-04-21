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

class CreateCollectionCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('avocado:create-collection')
            ->setDefinition(array(
                new InputArgument('collectionName' , InputArgument::REQUIRED, 'Collection name'),
            ))
            ->setDescription('Creates an Avocado Collection');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $connection = $this->getContainer()->get('mop_avocado.default_connection');
        $handler = new CollectionHandler($connection);
        $collection = new Collection();

        $collectionName = $input->getArgument('collectionName');
        $collection->setName($collectionName);
        $id = $handler->add($collection);
        $output->writeLn('Collection '.$collectionName.' has been created. Id: '.$id);
    }
}
