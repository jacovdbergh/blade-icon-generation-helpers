<?php

namespace Codeat3\BladeIconGeneration;

use Exception;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Symfony\Component\Filesystem\Filesystem;

class FetchLatest
{
    private Filesystem $filesystem;

    private $config;
    private $output;

    const CHECKOUT_DIR = 'dist';

    public function __construct($config, $output)
    {
        $this->filesystem = new Filesystem();
        $this->config = $config;
        $this->output = $output;
    }

    public static function create(array $config, $output):self
    {
        return new self($config, $output);
    }

    private function getBranchName()
    {
        return $this->config['branch'] ?? 'master';
    }

    private function getRepository()
    {
        return $this->config['repository'] ?? new Exception();
    }

    private function getWhitelistedDir()
    {
        return $this->config['whitelisted-dir'] ?? null;
    }

    private function pullUpdates()
    {
        $process = new Process([
            'git',
            'reset',
            '--hard',
            'origin/'.$this->getBranchName()
        ], self::CHECKOUT_DIR);
        $process->run();
        $this->output->writeln($process->getOutput());

        $process = new Process([
            'git',
            'pull',
            'origin',
            $this->getBranchName(),
            '--rebase',
            '--allow-unrelated-histories'
        ], self::CHECKOUT_DIR);
        $process->run();
        $this->output->writeln($process->getOutput());
    }

    private function cloneAndFetch()
    {
        // create a directory
        $this->filesystem->mkdir(self::CHECKOUT_DIR);

        // clone with only whitelisted directories
        $process = new Process([
            'git',
            'init',
            '--initial-branch='.$this->getBranchName(),
        ], self::CHECKOUT_DIR);
        $process->run();
        $this->output->writeln($process->getOutput());

        $process = new Process([
            'git',
            'remote',
            'add',
            'origin',
            $this->getRepository()
        ], self::CHECKOUT_DIR);
        $process->run();
        $this->output->writeln($process->getOutput());


        if ($this->getWhitelistedDir()) {
            $process = new Process([
                'git',
                'config',
                'core.sparseCheckout',
                'true',
            ], self::CHECKOUT_DIR);
            $process->run();
            $this->output->writeln($process->getOutput());

            $sparseCheckoutPath = Str::finish(self::CHECKOUT_DIR, DIRECTORY_SEPARATOR). '.git/info/sparse-checkout';
            file_put_contents($sparseCheckoutPath, $this->getWhitelistedDir(), FILE_APPEND);
        }

        $process = new Process([
            'git',
            'pull',
            '--depth=1',
            'origin',
            $this->getBranchName()
        ], self::CHECKOUT_DIR);
        $process->run();
        $this->output->writeln($process->getOutput());
    }

    public function fetch()
    {

        // check if dist exists
        if ($this->filesystem->exists(self::CHECKOUT_DIR)) {
            $this->output->writeln("Directory found, pulling it now !");
            $this->pullUpdates();
            $this->output->writeln("Pull Done !");
        } else {
            $this->output->writeln("Directory not found, clone & fetch !");
            $this->cloneAndFetch();
            $this->output->writeln("Clone & fetch done !");
        }
    }
}
