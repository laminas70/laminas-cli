<?php

declare(strict_types=1);

namespace LaminasTest\Cli\Input;

use Laminas\Cli\Input\BoolParam;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\ConfirmationQuestion;

use function sprintf;

class BoolParamTest extends TestCase
{
    /** @var BoolParam */
    private $param;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->param = new BoolParam('test');
    }

    /**
     * @return void
     */
    public function testUsesValueNoneOptionMode()
    {
        $this->assertSame(InputOption::VALUE_NONE, $this->param->getOptionMode());
    }

    /**
     * @psalm-return iterable<non-empty-string,array{0:bool,1:string}>
     * @return mixed[]
     */
    public function defaultValues()
    {
        yield 'false' => [false, 'y/N'];
        yield 'true'  => [true, 'Y/n'];
    }

    /**
     * @dataProvider defaultValues
     * @param bool $default
     * @param string $expectedDefaultString
     * @return void
     */
    public function testReturnsConfirmationQuestionUsingDescriptionAndDefault(
        $default,
        $expectedDefaultString
    ) {
        $description = 'This is the option description';
        $this->param->setDefault($default);
        $this->param->setDescription($description);
        $expected = sprintf(
            '<question>%s?</question> [<comment>%s</comment>]',
            $description,
            $expectedDefaultString
        );

        $question = $this->param->getQuestion();
        $this->assertInstanceOf(ConfirmationQuestion::class, $question);
        $this->assertSame($question->getQuestion(), $expected);
    }
}
