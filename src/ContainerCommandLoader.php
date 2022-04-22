<?php

declare(strict_types=1);

namespace Laminas\Cli;

use Symfony\Component\Console\Command\Command;

/**
 * @internal
 */
final class ContainerCommandLoader extends AbstractContainerCommandLoader
{
    /**
     * @param string $name
     */
    public function has($name)
    {
        return $this->hasCommand($name);
    }

    /**
     * @param string $name
     */
    public function get($name)
    {
        return $this->getCommand($name);
    }
}
