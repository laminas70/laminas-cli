<?php

declare(strict_types=1);

namespace LaminasTest\Cli;

use Laminas\Cli\ApplicationFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;

/** @psalm-suppress PropertyNotSetInConstructor */
class ApplicationFactoryTest extends TestCase
{
    /**
     * @return void
     */
    public function testPullsEventDispatcherFromContainerWhenPresent()
    {
        $this->assertInstanceOf(Application::class, (new ApplicationFactory())());
    }

    /**
     * @return void
     */
    public function testApplicationDefinitionContainsContainerOptionSoItIsAvailableForEveryCommand()
    {
        $application = (new ApplicationFactory())();
        $definition  = $application->getDefinition();
        self::assertTrue($definition->hasOption(ApplicationFactory::CONTAINER_OPTION));
    }
}
