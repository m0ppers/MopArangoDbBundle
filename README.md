# MopAvocadoBundle

This is a simple integration of AvocadoDB (http://avocadodb.org) into Symfony2

## Status

This is HIGHLY experimental as is AvocadoDB itself and its somewhat hackish xD

## What does the integration do?

This bundle handles several things:

1. It makes avocado connections configurable in symfony
2. It provides a neat DataCollector so you will see what's happening in the symfony profiler
3. It comes with a FOSUser integration (which should be in a separate bundle but whatever)
4. Some commands to handle creating and dropping collections (Couldn't figure out how to do that with avocsh)

## Installation

It's a normal symfony bundle so installation should be straight forward. However there is no composer integration yet
due to its experimental status.

You will need the AvocadoDB-PHP lib as well (easily installable through composer):

https://github.com/triAGENS/AvocadoDB-PHP

## Basic Configuration

### Connections
in app/config/config.yml:

mop_avocado:
    default_connection: main # optional will be set to the first connection if not present
    connections:
        main: 
            host: 127.0.0.1
            port: 8529

Should be pretty obvious. Once configured the Bundle handles the lazy loading of the connections.


You can now access your avocado connections using the DI-Container of sf2:

$connection = $container->get('mop_avocado.default_connection');

or

$connection = $container->get('mop_avocado.connections.main');

### FOS Userbundle integration

mop_avocado:
    fos:
        connection: main
        collection: users

Afterwards create the collection by issueing

php app/console avocado:create-collection users

and proceed with the normal userbundle configuration

## Avocado Helper Commands

Currently there are two helper commands available through the symfony console:

avocado:create-collection
avocado:drop-collection
