<?php

declare(strict_types=1);

namespace LaminasTest\Cli\TestAsset;

use Laminas\Cli\Command\AbstractParamAwareCommand;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractParamAwareCommandStub extends AbstractParamAwareCommand
{
    /** @var null|InputInterface */
    public $input;

    /** @var null|OutputInterface */
    public $output;

    /** @var array */
    public $options = [];

    /** @var HelperSet */
    private $helperSet;

    public function __construct(HelperSet $helperSet)
    {
        $this->helperSet = $helperSet;
        parent::__construct('test');
    }

    /**
     * @param string|array|null $shortcut
     * @param null|mixed        $default Defaults to null.
     * @return $this
     * @param string $name
     * @param int|null $mode
     * @param string $description
     */
    protected function doAddOption(
        $name,
        $shortcut = null,
        $mode = null,
        $description = '',
        $default = null
    ) {
        $this->options[$name] = [
            'shortcut'    => $shortcut,
            'mode'        => $mode,
            'description' => $description,
            'default'     => $default,
        ];
        return $this;
    }

    /**
     * @return \Symfony\Component\Console\Helper\HelperSet|null
     */
    public function getHelperSet()
    {
        return $this->helperSet;
    }

    /**
     * @return void
     */
    protected function configure()
    {
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function execute($input, $output): int
    {
        $this->input  = $input;
        $this->output = $output;
        return 0;
    }
}
