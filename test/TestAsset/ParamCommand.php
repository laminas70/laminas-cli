<?php

declare(strict_types=1);

namespace LaminasTest\Cli\TestAsset;

use Laminas\Cli\Command\AbstractParamAwareCommand;
use Laminas\Cli\Input\IntParam;
use Laminas\Cli\Input\ParamAwareInputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Webmozart\Assert\Assert;

class ParamCommand extends AbstractParamAwareCommand
{
    /**
     * @return void
     */
    protected function configure()
    {
        $this->addParam(
            (new IntParam('int-param'))
                ->setDescription('Param description')
                ->setRequiredFlag(true)
                ->setMin(1)
                ->setMax(10)
        );
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function execute($input, $output): int
    {
        Assert::isInstanceOf($input, ParamAwareInputInterface::class);

        /** @var int $int */
        $int = $input->getParam('int-param');
        $output->writeln('Int param value: ' . $int);

        return 0;
    }
}
