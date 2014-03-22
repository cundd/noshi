<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 22.03.14
 * Time: 12:15
 */

namespace Cundd\Noshi\Command;

/**
 * Abstract controller for CLI tools
 *
 * @package Cundd\Noshi\Command
 */
abstract class AbstractCommandController {
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
	protected $rawArguments = array();

	/**
	 * Prepared arguments
	 *
	 * @var array
	 */
	protected $arguments = array();

	/**
	 * Command
	 *
	 * The first element of the arguments
	 *
	 * @var string
	 */
	protected $command = '';

	function __construct($arguments) {
		$this->rawArguments = $arguments;

	}

	/**
	 * Invokes the correct command
	 */
	public function dispatch() {
		$arguments = $this->rawArguments;
		$this->command = array_shift($arguments);
		$this->arguments = $this->parseArguments($arguments);

		$commandName = $this->command . 'Command';

		if (is_callable(array($this, $commandName))) {
			$exitCode = call_user_func_array(array($this, $commandName), $this->arguments);
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
	public function parseArguments($arguments) {
		$parsedArguments = array();
		$argumentsCount = count($arguments);
		for ($i = 0; $i < $argumentsCount; $i++) {
			$currentArgument = $arguments[$i];
			switch (TRUE) {
				case substr($currentArgument, 0, 2) === '--':
					$parsedArguments[substr($currentArgument, 2)] = TRUE;
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
	 * Prints the given message to the console and adds a newline at the end
	 *
	 * @param string $message
	 */
	public function outputLine($message) {
		$this->output($message . PHP_EOL);
	}

	/**
	 * Prints the given message to the console
	 *
	 * @param string $message
	 */
	public function output($message) {
		fwrite(STDOUT, $message);
	}

	/**
	 * Prints the given error to the console
	 *
	 * @param string|\Exception $error
	 */
	public function outputError($error) {
		$message = self::ESCAPE . self::RED;
		if (is_scalar($error)) {
			$message .= $error;
		} else if (is_object($error) && $error instanceof \Exception) {
			$message .= '#' . $error->getCode() . ': ' .$error->getMessage();
		}
		$message .= PHP_EOL;
		$message .= self::ESCAPE . self::NORMAL;
		fwrite(STDERR, $message);
	}


}