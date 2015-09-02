<?php
/**
 * Created by PhpStorm.
 * User: mglaman
 * Date: 9/2/15
 * Time: 4:22 AM
 */

namespace mglaman\PlatformDocker\Command\Project;


use mglaman\Docker\Docker;
use mglaman\PlatformDocker\Command\Command;
use mglaman\PlatformDocker\Utils\Platform\Platform;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\ProcessBuilder;

class BehatCommand extends Command
{
    protected $behatLocation;
    protected $containerName = 'selenium-firefox';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
          ->setName('project:behat')
          ->setAliases(['behat'])
          ->setDescription('Runs behat');
    }

    /**
     * @inheritdoc
     */
    protected function initialize(InputInterface $input, OutputInterface $output) {
        parent::initialize($input, $output);

        $behatYmls = [];
        foreach ($this->discoverBehatYml() as $file) {
            /* @var $file \Symfony\Component\Finder\SplFileInfo */
            $behatYmls[] = $file->getRealPath();
        }
        if (count($behatYmls) == 0) {
            $output->writeln('Unable to find behat.yml in project');
            exit(1);
        }

        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion(
            'Select the Behat configuration to use',
            $behatYmls,
            0
        );
        $this->behatLocation = $helper->ask($input, $output, $question);
        $output->writeln("<comment>Using behat.yml: {$this->behatLocation}");
    }

    /**
     * {@inheritdoc}
     *
     * @see PlatformCommand::getCurrentEnvironment()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $now = new \DateTime();
        // Hopefully this isn't too opinionated?
        $workingDir = dirname($this->behatLocation);

        if (!is_dir($workingDir . '/vendor')) {
            $output->writeln("<error>Assumed behat directory doesn't have depednencies installed: $workingDir.");
            exit(1);
        }

        // Try to find the Behat binary
        $composerData = json_decode(file_get_contents($workingDir . '/composer.json'), true);
        if (isset($composerData['config']['bin-dir'])) {
            $binDir = $composerData['config']['bin-dir'];
        } else {
            $binDir = 'vendor/bin/';
        }

        $outPath = Platform::rootDir() . '/behat-run-' . $now->getTimestamp() . '.log';

        $builder = ProcessBuilder::create([
          $binDir . 'behat',
          '--format=pretty',
          '--out=' . $outPath,
        ]);
        $builder->setWorkingDirectory($workingDir);
        $builder->setTimeout(null);
        $process = $builder->getProcess();

        // @todo: export environment variables (postponed.)
        // @note there's also v2 v3 issues we'd have to sort out for exporting.
        //          just run the dang thing.

        $output->writeln("<info>Running Behat, saving output to $outPath");
        $this->startSeleniumContainer();
        $process->run();
        $this->stopSeleniumContainer();

        if (!$process->isSuccessful()) {
            $output->writeln($process->getErrorOutput());
        }
    }

    protected function discoverBehatYml()
    {
        $scanDirs = [
            Platform::sharedDir(),
            Platform::webDir(),
            Platform::rootDir() . '/tests',
        ];

        if (is_dir(Platform::repoDir())) {
            $scanDirs[] = Platform::repoDir();
        }

        $finder = new Finder();
        $finder->files()
          ->in($scanDirs)
          ->name('behat.yml');
        return $finder;
    }

    protected function startSeleniumContainer()
    {
        $this->stdOut->writeln("<comment>Starting the Selenium container");
        try {
            return !Docker::start([$this->containerName])->isSuccessful();
        } catch (\Exception $e) {
            $this->stdOut->writeln("<comment>Creating the Selenium container");
            return Docker::run([
              '-d',
              '-p',
              '4444:4444',
              '-v',
              '/dev/shm:/dev/shm',
              '--name',
              $this->containerName,
              'selenium/standalone-firefox:2.47.1',
            ]);
        }
    }

    protected function stopSeleniumContainer()
    {
        $this->stdOut->writeln("<comment>Stopping the Selenium container");
        return Docker::stop([$this->containerName]);
    }
}
