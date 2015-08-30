# platform-docker
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

This CLI will only run when within a Platform.sh project. When in a project, use ````platform-docker```` for a list of commands.

Sites are provisioned at project-name.platform. Currently the tld is not configurable. It's best to set up dnsmasq set up
wildcard DNS entries to point \*.platform to your localhost or Docker VM (Mac, Windows.)

If you are on Mac OS X, export ````PLATFORM_DOCKER_MACHINE_NAME```` with your Docker machine name. The tool will automatically boot the machine 
or export its environment information as needed.

### Features

#### Solr
By default an Apache Solr container is launched. The default server URI is ````http://solr:8983/solr````

### Commands

````
Available commands:
  help                Displays help for a command
  link                Displays link to local environment, with port.
  list                Lists commands
  start               Starts the docker containers
  stop                Stops the docker containers
 docker
  docker:init         Setup the Platform.sh Docker Compose files
  docker:logs         Tails the logs of a specific service container
  docker:rebuild      Rebuild configurations and containers
  docker:ssh          Allows for quick SSH into a service container.
  docker:stop         Stops the docker containers
  docker:up           Starts the docker containers
 flamegraph
  flamegraph:create   Creates a flamegraph from xhprof folder contents.
  flamegraph:setup    Sets the project up for generating flamegrapghs.
  flamegraph:unpatch  Unpatches index.php to stop xhprof logging.
 platform
  platform:db-sync    Syncs database from environment to local
````
