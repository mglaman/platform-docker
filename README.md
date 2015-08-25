# platformsh-docker
**Platform Docker** is a CLI tool for scaffolding docker-compose configuration for Platform.sh projects. 

## Installation

````
composer global require platformsh/docker:@stable
````

## Usage
This CLI will only run when within a Platform.sh project. When in a project, use ````platform-docker```` for a list of commands.

### Commands

````
Available commands:
  help              Displays help for a command
  link              Displays link to local environment, with port.
  list              Lists commands
 docker
  docker:init       Setup the Platform.sh Docker Compose files
  docker:stop       Stops the docker containers
  docker:up         Starts the docker containers
 platform
  platform:db-sync  Syncs database from environment to local
````
