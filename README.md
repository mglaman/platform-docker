# platform-docker [![Build Status](https://travis-ci.org/mglaman/platform-docker.svg?branch=master)](https://travis-ci.org/mglaman/platform-docker)
**Platform Docker** is a CLI tool for scaffolding docker-compose configuration for PHP projects, currently opinionated at Drupal (7 and 8!) Running ````platform-docker````
in a directory will create a multi-container application environment for local development.

## Requirements

* [Composer](https://getcomposer.org/)
* [Docker](https://www.docker.com/)
* [Docker Compose](https://docs.docker.com/compose/)

## Installation

First, if you do not have Docker then head over to their [documentation](https://docs.docker.com/) and see how to install for your machine.

````
composer global require mglaman/platform-docker:@stable
````

## Usage

Use within any directory. Until the app itself can scaffold a folder, it's expecting a folder structure of
* /shared (if not present it will be made)
* /www (required, this is your build)
* /repository (not required, but opinionated this is the source of what was built.)
* /tests (default directory it will look for Behat tests, however checks shared and www)

Sites are provisioned at *project-name*.platform. Currently the tld is not configurable (#24). It's best to set up dnsmasq set up wildcard DNS entries to point \*.platform to your localhost or Docker VM (Mac, Windows.) Here's some tutorials
* http://passingcuriosity.com/2013/dnsmasq-dev-osx/
* http://www.dickson.me.uk/2012/03/26/setting-up-dnsmasq-with-ubuntu-10-04-for-home-networking/

If you are on Mac OS X, export ````PLATFORM_DOCKER_MACHINE_NAME```` with your Docker machine name. The tool will automatically boot the machine or export its environment information as needed. For example, put ````12 export PLATFORM_DOCKER_MACHINE_NAME="vmname"```` in your .bash_profile.

### Features

#### Redis
There is a redis container available. Currently it can be added by adding the following to .platform-project in the root directory of the project

````
services:
  - redis
`````

#### Solr
An Apache Solr container is available with the default server URI is ````http://solr:8983/solr```` Currently it can be added by adding the following to .platform-project in the root directory of the project

````
services:
  - solr
`````

#### Flamegraphs
There is a helper command which patches Drupal to log xhprof items, and then turn them into a flamegraph.

#### Behat tests
Searches for behat.yml files, laucnches a Selenium (Firefox) container and executes tests.

### Commands

````
Available commands:
  behat               Runs behat
  help                Displays help for a command
  init                Setup Platform and Docker Compose files
  link                Displays link to local environment, with port.
  list                Lists commands
  proxy               Starts the nginx proxy container
  start               Starts the docker containers
  stop                Stops the docker containers
 docker
  docker:logs         Tails the logs of a specific service container
  docker:proxy        Starts the nginx proxy container
  docker:rebuild      Rebuild configurations and containers
  docker:ssh          Allows for quick SSH into a service container.
  docker:stop         Stops the docker containers
  docker:up           Starts the docker containers
 flamegraph
  flamegraph:create   Creates a flamegraph from xhprof folder contents.
  flamegraph:setup    Sets the project up for generating flamegrapghs.
  flamegraph:unpatch  Unpatches index.php to stop xhprof logging.
 project
  project:behat       Runs behat
  project:db-sync     Syncs database from environment to local
````
