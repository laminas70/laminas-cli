<?php

declare(strict_types=1);

namespace Local;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LocalCommand extends Command
{
    /** @var string|null */
    protected static $defaultName = 'local:command';

    /**
     * @return void
     */
    public function configure()
    {
        $this->setDescription('local command');
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
