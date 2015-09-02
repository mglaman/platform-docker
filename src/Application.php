<?php

namespace mglaman\PlatformDocker;

use Symfony\Component\Console\Application as ParentApplication;
use Symfony\Component\Console\Helper\DebugFormatterHelper;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\ProcessHelper;
use Symfony\Component\Console\Helper\QuestionHelper;

class Application extends ParentApplication
{
    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        parent::__construct('Platform Docker', '0.0.1');
        $this->setDefaultTimezone();
        $this->addCommands($this->getCommands());
    }

    /**
     * @return \Symfony\Component\Console\Command\Command[]
     */
    public function getCommands()
    {
        static $commands = array();
        if (count($commands)) {
            return $commands;
        }

        $commands[] = new Command\InitCommand();
        $commands[] = new Command\LinkCommand();
        $commands[] = new Command\Docker\UpCommand();
        $commands[] = new Command\Docker\StopCommand();
        $commands[] = new Command\Docker\SshCommand();
        $commands[] = new Command\Docker\LogsCommand();
        $commands[] = new Command\Docker\ProxyCommand();
        $commands[] = new Command\Docker\RebuildCommand();
        $commands[] = new Command\Flamegraph\SetupCommand();
        $commands[] = new Command\Flamegraph\CreateCommand();
        $commands[] = new Command\Flamegraph\UnpatchCommand();
        $commands[] = new Command\Project\DbSyncCommand();
        $commands[] = new Command\Project\BehatCommand();
        return $commands;
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultHelperSet()
    {
        return new HelperSet(array(
          new FormatterHelper(),
          new DebugFormatterHelper(),
          new ProcessHelper(),
          new QuestionHelper(),
        ));
    }


    /**
     * Set the default timezone.
     *
     * PHP 5.4 has removed the autodetection of the system timezone,
     * so it needs to be done manually.
     * UTC is the fallback in case autodetection fails.
     */
    protected function setDefaultTimezone()
    {
        $timezone = 'UTC';
        if (is_link('/etc/localtime')) {
            // Mac OS X (and older Linuxes)
            // /etc/localtime is a symlink to the timezone in /usr/share/zoneinfo.
            $filename = readlink('/etc/localtime');
            if (strpos($filename, '/usr/share/zoneinfo/') === 0) {
                $timezone = substr($filename, 20);
            }
        } elseif (file_exists('/etc/timezone')) {
            // Ubuntu / Debian.
            $data = file_get_contents('/etc/timezone');
            if ($data) {
                $timezone = trim($data);
            }
        } elseif (file_exists('/etc/sysconfig/clock')) {
            // RHEL/CentOS
            $data = parse_ini_file('/etc/sysconfig/clock');
            if (!empty($data['ZONE'])) {
                $timezone = trim($data['ZONE']);
            }
        }
        date_default_timezone_set($timezone);
    }
}
