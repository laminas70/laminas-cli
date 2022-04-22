<?php

declare(strict_types=1);

namespace LaminasTest\Cli\Input;

use InvalidArgumentException;
use Laminas\Cli\Input\AbstractInputParam;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Console\Question\Question;

class AbstractInputParamTest extends TestCase
{
    /** @var AbstractInputParam */
    private $param;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->param = new class ('test') extends AbstractInputParam {
            // phpcs:ignore WebimpressCodingStandard.Functions.ReturnType.InvalidNoReturn
            public function getQuestion(): Question
            {
                throw new RuntimeException('getQuestion should not be called');
            }
        };
    }

    /**
     * @return void
     */
    public function testDescriptionIsEmptyByDefault()
    {
        $this->assertSame('', $this->param->getDescription());
    }

    /**
     * @return void
     */
    public function testCanSetAndRetrieveDescription()
    {
        $description = 'This is the description';
        $this->param->setDescription($description);
        $this->assertSame($description, $this->param->getDescription());
    }

    /**
     * @return void
     */
    public function testDefaultValueIsNullByDefault()
    {
        $this->assertNull($this->param->getDefault());
    }

    /**
     * @return void
     */
    public function testCanSetAndRetrieveDefaultValue()
    {
        $default = 'This is the default value';
        $this->param->setDefault($default);
        $this->assertSame($default, $this->param->getDefault());
    }

    /**
     * @return void
     */
    public function testCanRetrieveName()
    {
        $this->assertSame('test', $this->param->getName());
    }

    /**
     * @return void
     */
    public function testNotRequiredByDefault()
    {
        $this->assertFalse($this->param->isRequired());
    }

    /**
     * @return void
     */
    public function testCanSetRequiredFlag()
    {
        $this->param->setRequiredFlag(true);
        $this->assertTrue($this->param->isRequired());
    }

    /**
     * @return void
     */
    public function testShortcutIsNullByDefault()
    {
        $this->assertNull($this->param->getShortcut());
    }

    /**
     * @psalm-return iterable<non-empty-string,array{0:mixed,1?:string}>
     * @return mixed[]
     */
    public function invalidShortcutValues()
    {
        yield 'bool'                   => [true];
        yield 'int'                    => [1];
        yield 'float'                  => [1.1];
        yield 'dashes only'            => ['--', 'non-zero-length'];
        yield 'spaces only'            => ['  ', 'non-zero-length'];
        yield 'object'                 => [(object) ['foo' => 'bar']];
        yield 'array with boolean'     => [[true], 'Only non-empty strings'];
        yield 'array with int'         => [[1], 'Only non-empty strings'];
        yield 'array with float'       => [[1.1], 'Only non-empty strings'];
        yield 'array with dashes only' => [['--'], 'must not be empty'];
        yield 'array with spaces only' => [['  '], 'must not be empty'];
        yield 'array with object'      => [[(object) ['foo' => 'bar']], 'Only non-empty strings'];
    }

    /**
     * @dataProvider invalidShortcutValues
     * @param mixed $shortcut
     * @param string $expectedMesage
     * @return void
     */
    public function testSettingShortcutShouldRaiseExceptionForInvalidValues(
        $shortcut,
        $expectedMesage = 'must be null, a non-zero-length string, or an array'
    ) {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMesage);
        /** @psalm-suppress MixedArgument */
        $this->param->setShortcut($shortcut);
    }

    /**
     * @psalm-return iterable<non-empty-string,array{0:null|list<string>|string}>
     * @return mixed[]
     */
    public function validShortcutValues()
    {
        yield 'null'                    => [null];
        yield 'string'                  => ['s'];
        yield 'dash string'             => ['-s'];
        yield 'multi-string'            => ['s|x'];
        yield 'array with string'       => [['s']];
        yield 'array with dash string'  => [['-s']];
        yield 'array with multi-string' => [['s|x']];
    }

    /**
     * @dataProvider validShortcutValues
     * @param mixed $shortcut
     * @psalm-param null|string|string[] $shortcut
     * @return void
     */
    public function testAllowsSettingShortcutWithValidValues($shortcut)
    {
        $this->param->setShortcut($shortcut);
        $this->assertSame($shortcut, $this->param->getShortcut());
    }
}
