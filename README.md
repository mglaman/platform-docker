# platform-docker [![Build Status](https://travis-ci.org/mglaman/platform-docker.svg?branch=master)](https://travis-ci.org/mglaman/platform-docker)
**Platform Docker** is a CLI tool for scaffolding docker-compose configuration for Platform.sh projects. Running ````platform-docker````
in a Platform.sh project folder will create a multi-container application environment for local development.

## Requirements

* [Composer](https://getcomposer.org/)
* [Docker](https://www.docker.com/)
* [Docker Compose](https://docs.docker.com/compose/)

## Installation

First, if you do not have Docker then head over to their [documentation](https://docs.docker.com/) and see how to install for your machine.

If you have platform-cli already running, then simply install via Composer.
````
composer global require mglaman/platform-docker:@stable
````

## Usage

Use within a Platform.sh, or any project. Until the app itself can scaffold a folder, it's expecting a folder structure of
* /
* /shared
* /www
* /repository

Sites are provisioned at project-name.platform. Currently the tld is not configurable. It's best to set up dnsmasq set up
wildcard DNS entries to point \*.platform to your localhost or Docker VM (Mac, Windows.)

If you are on Mac OS X, export ````PLATFORM_DOCKER_MACHINE_NAME```` with your Docker machine name. The tool will automatically boot the machine 
or export its environment information as needed.

### Features

#### Solr
By default an Apache Solr container is launched. The default server URI is ````http://solr:8983/solr````

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
