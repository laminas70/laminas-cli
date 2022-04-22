<?php

declare(strict_types=1);

namespace LaminasTest\Cli\Input;

use InvalidArgumentException;
use Laminas\Cli\Input\IntParam;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputOption;

use const PHP_EOL;

class IntParamTest extends TestCase
{
    /** @var IntParam */
    private $param;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->param = new IntParam('test');
        $this->param->setDescription('A number');
    }

    /**
     * @return void
     */
    public function testUsesValueRequiredOptionMode()
    {
        $this->assertSame(InputOption::VALUE_REQUIRED, $this->param->getOptionMode());
    }

    /**
     * @psalm-return iterable<non-empty-string,array{0:int|null,1:string}>
     * @return mixed[]
     */
    public function defaultValues()
    {
        $question = '<question>A number:</question>';
        $suffix   = PHP_EOL . ' > ';

        yield 'null' => [null, $question . $suffix];
        yield 'integer' => [1, $question . ' [<comment>1</comment>]' . $suffix];
    }

    /**
     * @dataProvider defaultValues
     * @param int|null $default
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
    public function testQuestionContainsANormalizer()
    {
        $normalizer = $this->param->getQuestion()->getNormalizer();
        $this->assertTrue(is_callable($normalizer));
    }

    /**
     * @psalm-return iterable<non-empty-string,array{0:numeric,1:int}>
     * @return mixed[]
     */
    public function numericInput()
    {
        yield 'string zero'    => ['0', 0];
        yield 'string integer' => ['1', 1];
        yield 'integer'        => [1, 1];
    }

    /**
     * @dataProvider numericInput
     * @param mixed $value
     * @param int $expected
     * @return void
     */
    public function testNormalizerCastsNumericValuesToIntegers($value, $expected)
    {
        $normalizer = $this->param->getQuestion()->getNormalizer();
        $this->assertTrue(is_callable($normalizer));
        $this->assertSame($expected, $normalizer($value));
    }

    /**
     * @psalm-return iterable<non-empty-string,array{0:mixed}>
     * @return mixed[]
     */
    public function nonNumericInput()
    {
        yield 'string'              => ['string'];
        yield 'string float zero'   => ['0.0'];
        yield 'string float'        => ['1.1'];
        yield 'float'               => [1.1];
    }

    /**
     * @dataProvider nonNumericInput
     * @param mixed $value
     * @return void
     */
    public function testNormalizerDoesNotCastNonNumericValues($value)
    {
        $normalizer = $this->param->getQuestion()->getNormalizer();
        $this->assertTrue(is_callable($normalizer));
        $this->assertSame($value, $normalizer($value));
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
        $this->expectExceptionMessage('Invalid value: integer expected');
        $validator(null);
    }

    /**
     * @dataProvider nonNumericInput
     * @param mixed $value
     * @return void
     */
    public function testValidatorRaisesExceptionIfRequiredAndNonNumeric($value)
    {
        $this->param->setRequiredFlag(true);
        $validator = $this->param->getQuestion()->getValidator();
        $this->assertTrue(is_callable($validator));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid value: integer expected');
        $validator($value);
    }

    /**
     * @return void
     */
    public function testValidatorRaisesExceptionIfRequiredAndBelowMinimum()
    {
        $this->param->setRequiredFlag(true);
        $this->param->setMin(10);
        $validator = $this->param->getQuestion()->getValidator();
        $this->assertTrue(is_callable($validator));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid value 1; minimum value is 10');
        $validator(1);
    }

    /**
     * @return void
     */
    public function testValidatorRaisesExceptionIfRequiredAndAboveMaximum()
    {
        $this->param->setRequiredFlag(true);
        $this->param->setMax(10);
        $validator = $this->param->getQuestion()->getValidator();
        $this->assertTrue(is_callable($validator));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid value 100; maximum value is 10');
        $validator(100);
    }

    /**
     * @return void
     */
    public function testValidatorReturnsValueVerbatimIfValueIsValid()
    {
        $this->param->setRequiredFlag(true);
        $this->param->setMin(1);
        $this->param->setMax(10);
        $validator = $this->param->getQuestion()->getValidator();

        $this->assertTrue(is_callable($validator));
        $this->assertSame(5, $validator(5));
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
