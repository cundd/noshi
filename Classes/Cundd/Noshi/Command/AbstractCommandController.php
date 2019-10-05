<?php
declare(strict_types=1);

namespace Cundd\Noshi\Command;

use Exception;
use ReflectionClass;
use ReflectionMethod;

/**
 * Abstract controller for CLI tools
 */
abstract class AbstractCommandController
{
    /**
     * ASCII command escape
     */
    const ESCAPE = "\033";

    /**
     * ASCII style normal
     */
    const NORMAL = "[0m";

    /**
     * ASCII color green
     */
    const GREEN = "[0;32m";

    /**
     * ASCII color red
     */
    const RED = "[0;31m";

    /**
     * Raw input arguments
     *
     * @var array
     */
    protected $rawArguments = [];

    /**
     * Prepared arguments
     *
     * @var array
     */
    protected $arguments = [];

    /**
     * Command
     *
     * The first element of the arguments
     *
     * @var string
     */
    protected $command = '';

    /**
     * Path to the script
     *
     * @var string
     */
    protected $scriptPath = '';

    function __construct($arguments)
    {
        $this->rawArguments = $arguments;
    }

    /**
     * Invokes the correct command
     */
    public function dispatch()
    {
        $arguments = $this->rawArguments;
        $this->scriptPath = array_shift($arguments);
        $this->command = array_shift($arguments);
        $this->arguments = $this->parseArguments($arguments);

        if (!$this->command) {
            $this->outputError('No command given');
            $this->command = 'help';
        }

        $commandName = $this->command . 'Command';
        if (is_callable([$this, $commandName])) {
            $exitCode = call_user_func_array([$this, $commandName], $this->arguments);
        } else {
            $this->outputError('Command ' . $this->command . ' could not be found');
            $exitCode = 1;
        }
        die($exitCode);
    }

    /**
     * @param $arguments
     * @return array
     */
    public function parseArguments($arguments)
    {
        $parsedArguments = [];
        $argumentsCount = count($arguments);
        for ($i = 0; $i < $argumentsCount; $i++) {
            $currentArgument = $arguments[$i];
            switch (true) {
                case substr($currentArgument, 0, 2) === '--':
                    $parsedArguments[substr($currentArgument, 2)] = true;
                    break;

                case substr($currentArgument, 0, 1) === '-':
                    $parsedArguments[substr($currentArgument, 1)] = $arguments[$i + 1];
                    $i++;
                    break;

                default:
                    $parsedArguments[] = $currentArgument;
            }
        }

        return $parsedArguments;
    }

    /**
     * Prints the given data in a table
     *
     * @param array  $data      Table data to display
     * @param string $delimiter Delimiter for table cells
     */
    public function outputTable($data, $delimiter = '|')
    {
        $output = '';
        $firstRow = reset($data);
        $columnWidths = [];

        $columnCount = count($firstRow);
        $headerRow = array_keys($firstRow);

        if (!(is_integer(reset($headerRow)) && is_integer(end($headerRow)))) {
            // Add the header row to the data to detect the longest string
            array_unshift($data, $headerRow);
        } else {
            $headerRow = [];
        }

        // Collect the column widths
        for ($columnIndex = 0; $columnIndex < $columnCount; $columnIndex++) {
            $columnWidths[$columnIndex] = max(
                array_map(
                    function ($row) use ($columnIndex) {
                        $row = array_values($row);

                        return (isset($row[$columnIndex]) ? strlen($row[$columnIndex]) : 0);
                    },
                    $data
                )
            );
        }

        /*
         * Print the header if it is defined
         */
        if ($headerRow) {
            // Remove the header row to the data after detecting the longest string
            array_shift($data);

            // Add the header
            $output .= $delimiter . ' ';
            foreach ($headerRow as $columnIndex => $cell) {
                $output .= ''
                    . str_pad($cell, $columnWidths[$columnIndex])
                    . ' ' . $delimiter . ' ';
            }
            $output .= PHP_EOL;

            // Add the line below the header
            $output .= $delimiter . ' ';
            foreach ($headerRow as $columnIndex => $cell) {
                $output .= ''
                    . str_repeat('-', $columnWidths[$columnIndex])
                    . ' ' . $delimiter . ' ';
            }
            $output .= PHP_EOL;
        }

        /*
         * Print the table
         */
        foreach ($data as $row) {
            $row = array_values($row);

            $output .= $delimiter . ' ';
            foreach ($row as $columnIndex => $cell) {
                if (is_bool($cell)) {
                    $cell = $cell ? 'TRUE' : 'FALSE';
                }
                $output .= ''
                    . str_pad($cell, $columnWidths[$columnIndex])
                    . ' ' . $delimiter . ' ';
            }
            $output .= PHP_EOL;
        }
        $this->output($output);
    }

    /**
     * Prints the given message to the console and adds a newline at the end
     *
     * @param string $message
     */
    public function outputLine($message)
    {
        $this->output($message . PHP_EOL);
    }

    /**
     * Prints the given message to the console
     *
     * @param string $message
     */
    public function output($message)
    {
        fwrite(STDOUT, $message);
    }

    /**
     * Prints the given error to the console
     *
     * @param string|Exception $error
     */
    public function outputError($error)
    {
        $message = self::ESCAPE . self::RED;
        if (is_scalar($error)) {
            $message .= $error;
        } else {
            if (is_object($error) && $error instanceof Exception) {
                $message .= '#' . $error->getCode() . ': ' . $error->getMessage();
            }
        }
        $message .= PHP_EOL;
        $message .= self::ESCAPE . self::NORMAL;
        fwrite(STDERR, $message);
    }

    /**
     * Displays this message
     */
    public function helpCommand()
    {
        $availableCommands = $this->getAvailableCommands();
        $longestCommandNameLength = max(
            array_map(
                function ($item) {
                    return strlen($item);
                },
                array_keys($availableCommands)
            )
        );

        $longestCommandNameLength += 4;
        foreach ($availableCommands as $command => $help) {
            $line = ''
                . self::ESCAPE . self::GREEN
                . str_pad($command, $longestCommandNameLength)
                . self::ESCAPE . self::NORMAL
                . $help;
            $this->outputLine($line);
        }
    }

    /**
     * Returns the available commands
     *
     * @return array
     */
    public function getAvailableCommands()
    {
        $commands = [];
        $classReflection = new ReflectionClass(get_class($this));
        $methods = $classReflection->getMethods(ReflectionMethod::IS_PUBLIC);
        /** @var ReflectionMethod $method */
        foreach ($methods as $method) {
            $methodDescription = [];
            $methodName = $method->name;
            $docComment = $method->getDocComment();
            $docCommentLines = explode(PHP_EOL, $docComment);

            if (substr($methodName, -7) !== 'Command') {
                continue;
            }

            foreach ($docCommentLines as $currentLine) {
                $currentLine = trim($currentLine);
                if (substr($currentLine, 0, 2) === '/*') {
                    continue;
                }

                if (substr($currentLine, 0, 1) === '*') {
                    $currentLine = substr($currentLine, 1);
                }

                $currentLine = trim($currentLine);
                if (!$currentLine) {
                    continue;
                }
                if ($currentLine[0] === '@') {
                    continue;
                }
                if ($currentLine === '/') {
                    continue;
                }
                $methodDescription[] = trim($currentLine);
            }
            $commands[substr($methodName, 0, -7)] = implode(' ', $methodDescription);
        }

        return $commands;
    }
}
