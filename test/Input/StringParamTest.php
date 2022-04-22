<?php

declare(strict_types=1);

namespace LaminasTest\Cli\Input;

use InvalidArgumentException;
use Laminas\Cli\Input\StringParam;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputOption;

use const PHP_EOL;

class StringParamTest extends TestCase
{
    /** @var StringParam */
    private $param;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->param = new StringParam('test');
        $this->param->setDescription('A string');
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
    public function defaultValues()
    {
        $question = '<question>A string:</question>';
        $suffix   = PHP_EOL . ' > ';

        yield 'null' => [null, $question . $suffix];
        yield 'string' => ['string', $question . ' [<comment>string</comment>]' . $suffix];
    }

    /**
     * @dataProvider defaultValues
     * @param string|null $default
     * @param string $expectedQuestionText
     * @return void
     */
    public function testCreatesStandardQuestionUsingDefaultValue(
        $default,
        $expectedQuestionText
    ) {
        $this->param->setDefault($default);
        $question = $this->param->getQuestion();
        $this->assertEquals($expectedQuestionText, $question->getQuestion());
    }

    /**
     * @return void
     */
    public function testQuestionContainsAValidator()
    {
        $validator = $this->param->getQuestion()->getValidator();
        $this->assertTrue(is_callable($validator));
    }

    /**
     * @return void
     */
    public function testValidatorRaisesExceptionIfValueIsNullAndRequired()
    {
        $this->param->setRequiredFlag(true);
        $validator = $this->param->getQuestion()->getValidator();
        $this->assertTrue(is_callable($validator));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid value: string expected');
        $validator(null);
    }

    /**
     * @return void
     */
    public function testValidatorReturnsValueVerbatimIfNoPatternProvided()
    {
        $this->param->setRequiredFlag(true);
        $validator = $this->param->getQuestion()->getValidator();

        $this->assertTrue(is_callable($validator));
        $this->assertSame('a string', $validator('a string'));
    }

    /**
     * @return void
     */
    public function testValidatorRaisesExceptionIfValueDoesNotMatchProvidedPattern()
    {
        $this->param->setRequiredFlag(true);
        $this->param->setPattern('/^[A-Z][a-zA-Z0-9_]+$/');
        $validator = $this->param->getQuestion()->getValidator();
        $this->assertTrue(is_callable($validator));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid value: does not match pattern');
        $validator('this does not match the pattern');
    }

    /**
     * @return void
     */
    public function testValidatorReturnsValueVerbatimIfMatchesPatternProvided()
    {
        $this->param->setRequiredFlag(true);
        $this->param->setPattern('/^[A-Z][a-zA-Z0-9_]+$/');
        $validator = $this->param->getQuestion()->getValidator();

        $this->assertTrue(is_callable($validator));
        $this->assertSame('AClassName', $validator('AClassName'));
    }

    /**
     * @return void
     */
    public function testSetPatternRaisesExceptionIfPatternIsInvalid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid PCRE pattern');

        $this->param->setPattern('This is#^ NOT** a! pattern,');
    }

    /**
     * @return void
     */
    public function testQuestionCreatedDoesNotIndicateMultiPromptByDefault()
    {
        $question = $this->param->getQuestion();
        self::assertNotContains(
            'Multiple entries allowed',
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
        self::assertContains(
            'Multiple entries allowed',
            $question->getQuestion()
        );
        self::assertNotContains(
            'At least one entry is required. ',
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
        self::assertContains(
            'Multiple entries allowed',
            $question->getQuestion()
        );
        self::assertContains(
            'At least one entry is required. ',
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
