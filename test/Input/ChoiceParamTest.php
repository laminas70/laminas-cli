<?php

declare(strict_types=1);

namespace LaminasTest\Cli\Input;

use Laminas\Cli\Input\ChoiceParam;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\ChoiceQuestion;

class ChoiceParamTest extends TestCase
{
    /** @var string[] */
    private $choices;

    /** @var ChoiceParam */
    private $param;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->choices = [
            'Red',
            'Green',
            'Blue',
        ];

        $this->param = new ChoiceParam(
            'test',
            $this->choices
        );
        $this->param->setDescription('Which color');
    }

    /**
     * @return void
     */
    public function testUsesValueRequiredOptionMode()
    {
        $this->assertSame(InputOption::VALUE_REQUIRED, $this->param->getOptionMode());
    }

    /**
     * @psalm-return iterable<non-empty-string,array{0:?string,1:string}>
     * @return mixed[]
     */
    public function defaultChoices()
    {
        $question = '<question>Which color?</question>';

        yield 'no default' => [null, $question];
        yield 'Red'        => ['Red', $question . ' [<comment>Red</comment>]'];
        yield 'Blue'       => ['Blue', $question . ' [<comment>Blue</comment>]'];
        yield 'Green'      => ['Green', $question . ' [<comment>Green</comment>]'];
    }

    /**
     * @dataProvider defaultChoices
     * @param string|null $default
     * @param string $expectedQuestionText
     * @return void
     */
    public function testQuestionReturnedIncludesChoicesAndDefault(
        $default,
        $expectedQuestionText
    ) {
        $this->param->setDefault($default);
        $question = $this->param->getQuestion();
        $this->assertInstanceOf(ChoiceQuestion::class, $question);
        $this->assertEquals($expectedQuestionText, $question->getQuestion());
        $this->assertSame($this->choices, $question->getChoices());
    }

    /**
     * @return void
     */
    public function testQuestionCreatedDoesNotIndicateMultiPromptByDefault()
    {
        $question = $this->param->getQuestion();
        self::assertStringNotContainsString(
            'Multiple selections allowed',
            $question->getQuestion()
        );
    }

    // phpcs:ignore Generic.Files.LineLength.TooLong
    /**
     * @return void
     */
    public function testQuestionCreatedIncludesMultiPromptButNotRequiredPromptWhenValueAllowsMultipleButNotRequired()
    {
        $this->param->setAllowMultipleFlag(true);
        $this->param->setRequiredFlag(false);
        $question = $this->param->getQuestion();
        self::assertStringContainsString(
            'Multiple selections allowed',
            $question->getQuestion()
        );
        self::assertStringNotContainsString(
            'At least one selection is required. ',
            $question->getQuestion()
        );
    }

    /**
     * @return void
     */
    public function testQuestionCreatedIncludesMultiPromptAndRequiredPromptWhenValueAllowsMultipleAndIsRequired()
    {
        $this->param->setAllowMultipleFlag(true);
        $this->param->setRequiredFlag(true);
        $question = $this->param->getQuestion();
        self::assertStringContainsString(
            'Multiple selections allowed',
            $question->getQuestion()
        );
        self::assertStringContainsString(
            'At least one selection is required. ',
            $question->getQuestion()
        );
    }

    /**
     * @return void
     */
    public function testCallingSetAllowMultipleWithBooleanFalseAfterPreviouslyCallingItWithTrueRemovesOptionFlag()
    {
        $this->param->setAllowMultipleFlag(true);
        self::assertSame(InputOption::VALUE_IS_ARRAY, $this->param->getOptionMode() & InputOption::VALUE_IS_ARRAY);
        $this->param->setAllowMultipleFlag(false);
        self::assertSame(0, $this->param->getOptionMode() & InputOption::VALUE_IS_ARRAY);
    }
}
