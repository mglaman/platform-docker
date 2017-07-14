# platform-docker [![Build Status](https://travis-ci.org/mglaman/platform-docker.svg?branch=master)](https://travis-ci.org/mglaman/platform-docker)

**Platform Docker** is a CLI tool for scaffolding docker-compose configuration for PHP projects, currently opinionated at PHP projects. Running ````platform-docker````
in a directory will create a multi-container application environment for local development.

Currently support is focused at Drupal 7 and Drupal 8. However there is rudimentary WordPress support. Generic PHP applications should be easy to implement.

## Requirements

* [Composer](https://getcomposer.org/)
* [Docker](https://www.docker.com/get-docker) (Community edition for most users)
* [Docker Compose](https://docs.docker.com/compose/)

## Installation

Ensure that you have all the requirements installed on your system, then run:

````
composer global require mglaman/platform-docker
````

## Usage

Use within any directory. Until the app itself can scaffold a folder, it's expecting a folder structure of

* /shared (if not present it will be made)
* /www (required, this is your build)
* /repository (not required, but opinionated this is the source of what was built.)
* /tests (default directory it will look for Behat tests, however checks shared and www)

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
  drush                              Runs a Drush command for environment.
  help                               Displays help for a command
  init                               Setup Platform and Docker Compose files
  link                               Displays link to local environment, with port.
  list                               Lists commands
 docker
  docker:logs                        Tails the logs of a specific service container
  docker:proxy (proxy)               Starts the nginx proxy container
  docker:rebuild                     Rebuild configurations and containers
  docker:restart (reboot)            Restarts the docker containers
  docker:ssh                         Allows for quick SSH into a service container.
  docker:stop (stop)                 Stops the docker containers
  docker:up (start)                  Starts the docker containers
 flamegraph
  flamegraph:create                  Creates a flamegraph from xhprof folder contents.
  flamegraph:setup                   Sets the project up for generating flamegrapghs.
  flamegraph:unpatch                 Unpatches index.php to stop xhprof logging.
 project
  project:behat (behat)              Runs behat test suite for project. Checks ./tests, ./www, ./shared and ./repository by default.
  project:db-sync                    Syncs database from environment to local
 provider
  provider:platformsh (platformsh)   Sets up a Platform.sh project
````
