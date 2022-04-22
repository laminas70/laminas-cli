<?php

declare(strict_types=1);

namespace LaminasTest\Cli\TestAsset;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ExampleCommandWithDependencies extends Command
{
    /** @var string|null */
    protected static $defaultName = 'example:command-with-deps';

    /** @var ExampleDependency */
    // phpcs:ignore SlevomatCodingStandard.Classes.UnusedPrivateElements
    private $dependency;

    public function __construct(ExampleDependency $dependency)
    {
        $this->dependency = $dependency;
        parent::__construct();
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this->setDescription('Test command with dependencies');
        $this->setHelp('Execute a test command that includes dependencies');
        $this->addOption(
            'string',
            's',
            InputOption::VALUE_REQUIRED,
            'A string option',
            $this->dependency->getDefault()
        );
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function execute($input, $output): int
    {
        return 0;
    }
}
