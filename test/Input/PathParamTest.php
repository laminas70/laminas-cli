<?php

declare(strict_types=1);

namespace LaminasTest\Cli\Input;

use InvalidArgumentException;
use Laminas\Cli\Input\PathParam;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputOption;

use function array_reduce;
use function count;
use function dirname;
use function realpath;
use function strpos;

use const PHP_EOL;

class PathParamTest extends TestCase
{
    /** @var PathParam */
    private $param;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->param = new PathParam('test', PathParam::TYPE_FILE);
        $this->param->setDescription('Selected path');
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
        $question = '<question>Selected path:</question>';
        $suffix   = PHP_EOL . ' > ';

        yield 'null' => [null, $question . $suffix];
        yield 'path' => ['path', $question . ' [<comment>path</comment>]' . $suffix];
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
    public function testQuestionContainsAnAutocompleter()
    {
        $this->param->setDefault('path');
        $question = $this->param->getQuestion();
        $this->assertTrue(is_callable($question->getAutocompleterCallback()));
    }

    /**
     * @return void
     */
    public function testQuestionContainsAValidator()
    {
        $this->param->setDefault('path');
        $question = $this->param->getQuestion();
        $this->assertTrue(is_callable($question->getValidator()));
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
    public function testValidatorReturnsValueVerbatimIfDoesNotExistAndAllowedNotToExist()
    {
        $validator = $this->param->getQuestion()->getValidator();
        $this->assertTrue(is_callable($validator));
        $this->assertSame('path-that-does-not-exist', $validator('path-that-does-not-exist'));
    }

    /**
     * @return void
     */
    public function testValidatorRaisesExceptionIfValueIsNonExistentPathAndMustExist()
    {
        $this->param->setPathMustExist(true);
        $validator = $this->param->getQuestion()->getValidator();
        $this->assertTrue(is_callable($validator));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Path does not exist');
        $validator('path-that-does-not-exist');
    }

    /**
     * @return void
     */
    public function testValidatorRaisesExceptionIfFileExistsButMustBeADirectory()
    {
        $param = new PathParam('test', PathParam::TYPE_DIR);
        $param->setPathMustExist(true);
        $validator = $param->getQuestion()->getValidator();
        $this->assertTrue(is_callable($validator));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Path is not a valid directory');
        $validator(__FILE__);
    }

    /**
     * @return void
     */
    public function testValidatorReturnsValueVerbatimIfFileExists()
    {
        $this->param->setPathMustExist(true);
        $validator = $this->param->getQuestion()->getValidator();

        $this->assertTrue(is_callable(($validator)));
        $this->assertSame(__FILE__, $validator(__FILE__));
    }

    /**
     * @return void
     */
    public function testValidatorReturnsValueVerbatimIfDirExists()
    {
        $param = new PathParam('test', PathParam::TYPE_DIR);
        $param->setPathMustExist(true);
        $validator = $param->getQuestion()->getValidator();

        $this->assertTrue(is_callable($validator));
        $this->assertSame(__DIR__, $validator(__DIR__));
    }

    /**
     * @return void
     */
    public function testAutocompleterReturnsFilesAndDirectoriesBasedOnProvidedInput()
    {
        $autocompleter = $this->param->getQuestion()->getAutocompleterCallback();
        $this->assertTrue(is_callable($autocompleter));

        $paths = $autocompleter(__DIR__);
        $this->assertTrue(is_array($paths));
        $this->assertGreaterThan(0, count($paths));

        $actual = array_reduce($paths, function (bool $isValid, string $path) {
            return $isValid && 0 === strpos($path, realpath(dirname(__DIR__)));
        }, true);

        $this->assertTrue($actual, 'One or more autocompletion paths were invalid');
    }

    /**
     * @return void
     */
    public function testConstructorRaisesExceptionForInvalidTypeValue()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid type provided');
        new PathParam('test', 'not-a-valid-type');
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
