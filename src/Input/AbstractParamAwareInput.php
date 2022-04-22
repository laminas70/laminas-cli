<?php

declare(strict_types=1);

namespace Laminas\Cli\Input;

use InvalidArgumentException;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\StreamableInputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Webmozart\Assert\Assert;

use function array_map;
use function array_walk;
use function get_debug_type;
use function in_array;
use function is_array;
use function sprintf;

/**
 * Decorate an input instance to add a `getParam()` method.
 *
 * @internal
 */
abstract class AbstractParamAwareInput implements ParamAwareInputInterface
{
    /** @var QuestionHelper */
    protected $helper;

    /** @var InputInterface */
    protected $input;

    /** @var OutputInterface */
    protected $output;

    /** @var array<string, InputParamInterface> */
    private $params;

    /**
     * @param array<string, InputParamInterface> $params
     */
    public function __construct(InputInterface $input, OutputInterface $output, QuestionHelper $helper, array $params)
    {
        $this->input  = $input;
        $this->output = $output;
        $this->helper = $helper;
        $this->params = $params;
    }

    /**
     * Define this method in order to modify the question, if needed, before
     * prompting for an answer.
     * @param \Symfony\Component\Console\Question\Question $question
     * @return void
     */
    abstract protected function modifyQuestion($question);

    /**
     * @return mixed
     * @throws InvalidArgumentException When the parameter does not exist.
     * @throws InvalidArgumentException When the parameter is of an invalid type.
     * @throws InvalidArgumentException When the parameter is required, input is
     *     non-interactive, and no value is provided.
     * @param string $name
     */
    final public function getParam($name)
    {
        if (! isset($this->params[$name])) {
            throw new InvalidArgumentException(sprintf('Invalid parameter name: %s', $name));
        }

        /** @psalm-suppress MixedAssignment */
        $value      = $this->input->getOption($name);
        $inputParam = $this->params[$name];

        $question = $inputParam->getQuestion();
        $this->modifyQuestion($question);

        if (
            ! $this->isParamValueProvided($inputParam, $value)
            && ! $this->input->isInteractive()
        ) {
            /** @psalm-suppress MixedAssignment */
            $value = $inputParam->getDefault();
        }

        $valueIsArray = (bool) ($inputParam->getOptionMode() & InputOption::VALUE_IS_ARRAY);
        if ($this->isParamValueProvided($inputParam, $value)) {
            /** @psalm-suppress MixedAssignment */
            $normalizedValue = $this->normalizeValue($value, $question->getNormalizer());
            $this->validateValue($normalizedValue, $valueIsArray, $question->getValidator(), $name);
            return $normalizedValue;
        }

        if (! $this->input->isInteractive() && $inputParam->isRequired()) {
            throw new InvalidArgumentException(sprintf('Missing required value for --%s parameter', $name));
        }

        // Prepend a validator that will skip validation of empty/null values
        // when the parameter is not required.
        $originalValidator = null;
        if (! $inputParam->isRequired()) {
            $originalValidator = $this->prependSkipValidator($question);
        }

        /** @var null|bool|string|array $value */
        $value = $this->askQuestion($question, $valueIsArray, $inputParam->isRequired());

        // Reset the validator if we prepended it earlier.
        if ($originalValidator) {
            $question->setValidator($originalValidator);
        }

        // set the option value so it can be reused in chains
        $this->input->setOption($name, $value);

        return $value;
    }

    /**
     * @return string|null
     */
    public function getFirstArgument()
    {
        return $this->input->getFirstArgument();
    }

    /**
     * @param \Symfony\Component\Console\Input\InputDefinition $definition
     * @return void
     */
    public function bind($definition)
    {
        $this->input->bind($definition);
    }

    /**
     * @return void
     */
    public function validate()
    {
        $this->input->validate();
    }

    /**
     * @return array<string|bool|int|float|array|null>
     */
    public function getArguments(): array
    {
        return $this->input->getArguments();
    }

    /**
     * @param string $name
     */
    public function hasArgument($name): bool
    {
        return $this->input->hasArgument($name);
    }

    /**
     * @return array<string|bool|int|float|array|null>
     */
    public function getOptions(): array
    {
        return $this->input->getOptions();
    }

    public function isInteractive(): bool
    {
        /** @psalm-suppress RedundantCastGivenDocblockType */
        return (bool) $this->input->isInteractive();
    }

    /**
     * @param resource $stream
     * @return void
     */
    public function setStream($stream)
    {
        if (! $this->input instanceof StreamableInputInterface) {
            return;
        }
        $this->input->setStream($stream);
    }

    /**
     * @return null|resource
     */
    public function getStream()
    {
        if (! $this->input instanceof StreamableInputInterface) {
            return null;
        }
        return $this->input->getStream();
    }

    /**
     * @param mixed $value
     */
    private function isParamValueProvided(InputParamInterface $param, $value): bool
    {
        $mode = $param->getOptionMode();

        if ($mode & InputOption::VALUE_IS_ARRAY) {
            return ! in_array($value, [null, []], true);
        }

        return $value !== null;
    }

    /**
     * @param mixed $value
     * @return mixed
     * @param callable|null $normalizer
     */
    private function normalizeValue($value, $normalizer)
    {
        // No normalizer: nothing to do
        if ($normalizer === null) {
            return $value;
        }

        // Non-array value: normalize it directly
        if (! is_array($value)) {
            return $normalizer($value);
        }

        // Array value: map each to the normalizer
        return array_map($normalizer, $value);
    }

    /**
     * @param mixed $value
     * @throws InvalidArgumentException When an array value is expected, but not
     *     provided.
     * @param callable|null $validator
     * @return void
     */
    private function validateValue(
        $value,
        bool $valueIsArray,
        $validator,
        string $paramName
    ) {
        // No validator: nothing to do
        if (! $validator) {
            return;
        }

        // Non-array value; validate it directly
        if (! $valueIsArray) {
            $validator($value);
            return;
        }

        // Array value expected, but not an array: raise an exception
        Assert::isArray($value, sprintf(
            'Option --%s expects an array of values, but received "%s";'
            . ' check to ensure the command has provided a valid default.',
            $paramName,
            get_debug_type($value)
        ));

        // Array value: validate each item in the array
        array_walk($value, $validator);
    }

    /**
     * @return mixed Returns result of asking question, or, if this is a
     *     multi-select, it loops until no more answers are provided, and retuns
     *     an array of results.
     */
    private function askQuestion(Question $question, bool $valueIsArray, bool $valueIsRequired)
    {
        if (! $valueIsArray) {
            return $this->helper->ask($this, $this->output, $question);
        }

        $validator = $question->getValidator();
        $value     = null;

        /** @var mixed[] $values */
        $values = [];

        do {
            if (null !== $value) {
                /** @psalm-suppress MixedAssignment */
                $values[] = $value;
            }

            /** @var mixed $value */
            $value = $this->helper->ask($this, $this->output, $question);

            if ($valueIsRequired && [] === $values) {
                $question->setValidator(
                /**
                 * @param mixed $value
                 * @return mixed
                 */
                    static function ($value) use ($validator) {
                        if (null === $value || '' === $value) {
                            return $value;
                        }

                        if (null === $validator) {
                            return $value;
                        }

                        return $validator($value);
                    }
                );
            }
        } while (! in_array($value, [null, ''], true));

        $question->setValidator($validator);

        return $values;
    }

    /**
     * @return callable|null
     */
    private function prependSkipValidator(Question $question)
    {
        $originalValidator = $question->getValidator();
        if (null === $originalValidator) {
            return null;
        }

        $question->setValidator(
        /**
         * @param mixed $value
         * @return mixed
         */
            static function ($value) use ($originalValidator) {
                if ($value === null) {
                    return null;
                }

                return $originalValidator($value);
            }
        );

        return $originalValidator;
    }
}
