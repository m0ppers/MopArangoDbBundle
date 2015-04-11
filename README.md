# MopArangoDbBundle

This is a simple integration of ArangoDb (http://arangodb.org) into Symfony2

## Status

This is HIGHLY experimental as is ArangoDb itself and its somewhat hackish xD

## What does the integration do?

This bundle handles several things:

1. It makes arangodb connections configurable in symfony
2. It provides a neat DataCollector so you will see what's happening in the symfony profiler
3. It comes with a FOSUser integration (which should be in a separate bundle but whatever)

## Installation

It's a normal symfony bundle so installation should be straight forward. There is no composer integration via packagist yet
due to its experimental status. You can install the bundle via composer easily nevertheless:

Step 1: Register this github repository in the repositories section of your composer.json
```
"repositories": [
        ...
        {
               "type":"vcs",
               "url":"https://github.com/m0ppers/MopArangoDbBundle.git"
        }
		...
   ],
```

Step 2: This bundle needs ArangoDB-PHP. Add both bundles to your composer.json:
```
 "require": {
	    ..
	    "mop/arangodbbundle" : "dev-master",
		"triagens/ArangoDb": "2.0.*",
		..
	}
```

ArangoDb-PHP comes with lots of documentation & examples. Check it out here:

https://github.com/triAGENS/ArangoDb-PHP

## Basic Configuration

### Connections
in app/config/config.yml:

```
mop_arango_db:
    default_connection: main # optional will be set to the first connection if not present
    connections:
        main: 
            host: 127.0.0.1
            port: 8529
```
Should be pretty obvious. Once configured the Bundle handles the lazy loading of the connections.


You can now access your avocado connections using the DI-Container of sf2:
```
$connection = $container->get('mop_arangodb.default_connection');

or

$connection = $container->get('mop_arangodb.connections.main');
```

### FOS Userbundle integration

```
mop_arango_db:
    fos:
        connection: main
        collection: users
```
Afterwards create the collection in arangodb.

Then you will have to tell fos that it should use the arangodb driver:

```
fos_user:
    db_driver: custom
    user_class: Acme\DemoBundle\Entity\User
    firewall_name: main
    service:
        user_manager: mop_arangodb.fos.user_manager
```
