<?php
/**
 * Created by PhpStorm.
 * User: alen
 * Date: 12/02/2018
 * Time: 6:56 PM
 */

namespace think\migration\adapter;


use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Output\OutputInterface;
use think\console\Output;

class OutputAdapter implements OutputInterface
{
    /**
     * @var Output
     */
    protected $output;
    protected $decorated;
    protected $formatter;
    protected $verbosity;

    public function __construct(Output $output)
    {
        $this->output = $output;
    }

    /**
     * Writes a message to the output.
     *
     * @param string|array $messages The message as an array of lines or a single string
     * @param bool         $newline  Whether to add a newline
     * @param int          $options  A bitmask of options (one of the OUTPUT or VERBOSITY constants), 0 is considered the same as self::OUTPUT_NORMAL | self::VERBOSITY_NORMAL
     */
    public function write($messages, $newline = false, $options = 0)
    {
        $type = $this->optionsToType($options);
        $this->output->write($messages, $newline, $type);
    }

    /**
     * Writes a message to the output and adds a newline at the end.
     *
     * @param string|array $messages The message as an array of lines of a single string
     * @param int          $options  A bitmask of options (one of the OUTPUT or VERBOSITY constants), 0 is considered the same as self::OUTPUT_NORMAL | self::VERBOSITY_NORMAL
     */
    public function writeln($messages, $options = 0)
    {
        $type = $this->optionsToType($options);
        $type = $this->transformCode($type);
        $this->output->writeln($messages, $type);
    }

    /**
     * Sets the verbosity of the output.
     *
     * @param int $level The level of verbosity (one of the VERBOSITY constants)
     */
    public function setVerbosity($level)
    {
        $this->verbosity = $level;
        $level           = $this->transformCode($level);
        $this->output->setVerbosity($level);
    }

    /**
     * Gets the current verbosity of the output.
     *
     * @return int The current level of verbosity (one of the VERBOSITY constants)
     */
    public function getVerbosity()
    {
        return $this->verbosity;
    }

    /**
     * Returns whether verbosity is quiet (-q).
     *
     * @return bool true if verbosity is set to VERBOSITY_QUIET, false otherwise
     */
    public function isQuiet()
    {
        return $this->output->isQuiet();
    }

    /**
     * Returns whether verbosity is verbose (-v).
     *
     * @return bool true if verbosity is set to VERBOSITY_VERBOSE, false otherwise
     */
    public function isVerbose()
    {
        return $this->output->isVerbose();
    }

    /**
     * Returns whether verbosity is very verbose (-vv).
     *
     * @return bool true if verbosity is set to VERBOSITY_VERY_VERBOSE, false otherwise
     */
    public function isVeryVerbose()
    {
        return $this->output->isVeryVerbose();
    }

    /**
     * Returns whether verbosity is debug (-vvv).
     *
     * @return bool true if verbosity is set to VERBOSITY_DEBUG, false otherwise
     */
    public function isDebug()
    {
        return $this->output->isDebug();
    }

    /**
     * Sets the decorated flag.
     *
     * @param bool $decorated Whether to decorate the messages
     */
    public function setDecorated($decorated)
    {
        $this->output->setDecorated($decorated);
        $this->decorated = $decorated;
    }

    /**
     * Gets the decorated flag.
     *
     * @return bool true if the output will decorate messages, false otherwise
     */
    public function isDecorated()
    {
        return $this->decorated;
    }

    public function setFormatter(OutputFormatterInterface $formatter)
    {
        $this->formatter = $formatter;
    }

    /**
     * Returns current output formatter instance.
     *
     * @return OutputFormatterInterface
     */
    public function getFormatter()
    {
        return $this->formatter;
    }

    private function optionsToType(int $options)
    {
        $types = self::OUTPUT_NORMAL | self::OUTPUT_RAW | self::OUTPUT_PLAIN;
        $type  = $types & $options ?: self::OUTPUT_NORMAL;
        $type  = $this->transformCode($type);


        $verbosities = self::VERBOSITY_QUIET | self::VERBOSITY_NORMAL | self::VERBOSITY_VERBOSE | self::VERBOSITY_VERY_VERBOSE | self::VERBOSITY_DEBUG;
        $verbosity   = $verbosities & $options ?: self::VERBOSITY_NORMAL;
        $this->setVerbosity($verbosity);

        return $type;
    }

    private function transformCode(int $code)
    {
        switch ($code) {
            case self::OUTPUT_NORMAL:
                $code = Output::OUTPUT_NORMAL;
                break;
            case self::OUTPUT_RAW:
                $code = Output::OUTPUT_RAW;
                break;
            case self::OUTPUT_PLAIN:
                $code = Output::OUTPUT_PLAIN;
                break;
            case self::VERBOSITY_QUIET:
                $code = Output::VERBOSITY_QUIET;
                break;
            case self::VERBOSITY_NORMAL:
                $code = Output::VERBOSITY_NORMAL;
                break;
            case self::VERBOSITY_VERBOSE:
                $code = Output::VERBOSITY_VERBOSE;
                break;
            case self::VERBOSITY_VERY_VERBOSE:
                $code = Output::VERBOSITY_VERY_VERBOSE;
                break;
            case self::VERBOSITY_DEBUG:
                $code = Output::VERBOSITY_DEBUG;
                break;
        }
        return $code;
    }
}