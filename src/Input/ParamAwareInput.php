<?php

declare(strict_types=1);

namespace Laminas\Cli\Input;

use Symfony\Component\Console\Question\Question;

/**
 * Decorate an input instance to add a `getParam()` method.
 *
 * @internal
 */
final class ParamAwareInput extends AbstractParamAwareInput
{
    /**
     * @param \Symfony\Component\Console\Question\Question $question
     * @return void
     */
    protected function modifyQuestion($question)
    {
        // deliberate no-op
    }

    /**
     * @param string|array $values
     * @param bool $onlyParams
     */
    public function hasParameterOption($values, $onlyParams = false): bool
    {
        return $this->input->hasParameterOption($values, $onlyParams);
    }

    /**
     * @param string|array                     $values
     * @param string|bool|int|float|array|null $default
     * @return mixed
     * @param bool $onlyParams
     */
    public function getParameterOption($values, $default = false, $onlyParams = false)
    {
        return $this->input->getParameterOption($values, $default, $onlyParams);
    }

    /**
     * @return mixed
     * @param string $name
     */
    public function getArgument($name)
    {
        return $this->input->getArgument($name);
    }

    /**
     * @param mixed $value
     * @param string $name
     * @return void
     */
    public function setArgument($name, $value)
    {
        $this->input->setArgument($name, $value);
    }

    /**
     * @return mixed
     * @param string $name
     */
    public function getOption($name)
    {
        return $this->input->getOption($name);
    }

    /**
     * @param mixed $value
     * @param string $name
     * @return void
     */
    public function setOption($name, $value)
    {
        $this->input->setOption($name, $value);
    }

    /**
     * @param string $name
     */
    public function hasOption($name): bool
    {
        return $this->input->hasOption($name);
    }

    /**
     * @param bool $interactive
     * @return void
     */
    public function setInteractive($interactive)
    {
        $this->input->setInteractive($interactive);
    }
}
