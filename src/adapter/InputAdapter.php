<?php
/**
 * Created by PhpStorm.
 * User: alen
 * Date: 12/02/2018
 * Time: 6:56 PM
 */

namespace think\migration\adapter;


use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use think\console\Input;

class InputAdapter implements InputInterface
{
    /**
     * @var Input
     */
    protected $input;

    public function __construct(Input $input)
    {
        $this->input = $input;
    }

    /**
     * Returns the first argument from the raw parameters (not parsed).
     *
     * @return string|null The value of the first argument or null otherwise
     */
    public function getFirstArgument()
    {
        return $this->input->getFirstArgument();
    }

    /**
     * Returns true if the raw parameters (not parsed) contain a value.
     *
     * This method is to be used to introspect the input parameters
     * before they have been validated. It must be used carefully.
     *
     * @param string|array $values     The values to look for in the raw parameters (can be an array)
     * @param bool         $onlyParams Only check real parameters, skip those following an end of options (--) signal
     *
     * @return bool true if the value is contained in the raw parameters
     */
    public function hasParameterOption($values, $onlyParams = false)
    {
        return $this->input->hasParameterOption($values);
    }

    /**
     * Returns the value of a raw option (not parsed).
     *
     * This method is to be used to introspect the input parameters
     * before they have been validated. It must be used carefully.
     *
     * @param string|array $values     The value(s) to look for in the raw parameters (can be an array)
     * @param mixed        $default    The default value to return if no result is found
     * @param bool         $onlyParams Only check real parameters, skip those following an end of options (--) signal
     *
     * @return mixed The option value
     */
    public function getParameterOption($values, $default = false, $onlyParams = false)
    {
        return $this->input->getParameterOption($values, $default);
    }

    /**
     * Binds the current Input instance with the given arguments and options.
     * @param InputDefinition $definition
     * @throws \ReflectionException
     */
    public function bind(InputDefinition $definition)
    {
        $adapter          = new InputDefinitionAdapter($definition);
        $options          = $definition->getOptions();
        $arguments        = $definition->getArguments();
        $definition_array = $options + $arguments;
        $adapter->setDefinition($definition_array);

        $this->input->bind($adapter);
    }

    /**
     * Validates the input.
     *
     * @throws RuntimeException When not enough arguments are given
     */
    public function validate()
    {
        $this->input->validate();
    }

    /**
     * Returns all the given arguments merged with the default values.
     *
     * @return array
     */
    public function getArguments()
    {
        return $this->input->getArguments();
    }

    /**
     * Returns the argument value for a given argument name.
     *
     * @param string $name The argument name
     *
     * @return mixed The argument value
     *
     * @throws InvalidArgumentException When argument given doesn't exist
     */
    public function getArgument($name)
    {
        return $this->input->getArgument($name);
    }

    /**
     * Sets an argument value by name.
     *
     * @param string $name  The argument name
     * @param string $value The argument value
     *
     * @throws InvalidArgumentException When argument given doesn't exist
     */
    public function setArgument($name, $value)
    {
        $this->input->setArgument($name, $value);
    }

    /**
     * Returns true if an InputArgument object exists by name or position.
     *
     * @param string|int $name The InputArgument name or position
     *
     * @return bool true if the InputArgument object exists, false otherwise
     */
    public function hasArgument($name)
    {
        return $this->input->hasArgument($name);
    }

    /**
     * Returns all the given options merged with the default values.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->input->getOptions();
    }

    /**
     * Returns the option value for a given option name.
     *
     * @param string $name The option name
     *
     * @return mixed The option value
     *
     * @throws InvalidArgumentException When option given doesn't exist
     */
    public function getOption($name)
    {
        return $this->input->getOption($name);
    }

    /**
     * Sets an option value by name.
     *
     * @param string      $name  The option name
     * @param string|bool $value The option value
     *
     * @throws InvalidArgumentException When option given doesn't exist
     */
    public function setOption($name, $value)
    {
        $this->input->setOption($name, $value);
    }

    /**
     * Returns true if an InputOption object exists by name.
     *
     * @param string $name The InputOption name
     *
     * @return bool true if the InputOption object exists, false otherwise
     */
    public function hasOption($name)
    {
        return $this->input->hasOption($name);
    }

    /**
     * Is this input means interactive?
     *
     * @return bool
     */
    public function isInteractive()
    {
        return $this->input->isInteractive();
    }

    /**
     * Sets the input interactivity.
     *
     * @param bool $interactive If the input should be interactive
     */
    public function setInteractive($interactive)
    {
        $this->input->setInteractive($interactive);
    }
}