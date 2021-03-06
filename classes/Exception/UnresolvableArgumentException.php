<?php
/**
 * This file is part of the Autarky package.
 *
 * (c) Andreas Lutro <anlutro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Autarky\Container\Exception;

use Exception;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionParameter;

/**
 * Exception that is thrown when an argument to a method that the container
 * needs to invoke, cannot be resolved.
 */
class UnresolvableArgumentException extends ContainerException
{
	/**
	 * Create a new exception from some reflection objects.
	 *
	 * @param  ReflectionParameter             $param
	 * @param  ReflectionFunctionAbstract|null $func
	 * @param  Exception|null                  $previous
	 * @param  string|null                     $afterMessage
	 *
	 * @return static
	 */
	public static function fromReflectionParam(
		ReflectionParameter $param,
		ReflectionFunctionAbstract $func = null,
		Exception $previous = null,
		$afterMessage = null
	) {
		$message = static::makeMessage($param, $func);

		if ($previous) {
			$message .= ' - '.$previous->getMessage();
		}

		if ($afterMessage) {
			$message .= ' - '.$afterMessage;
		}

		return new static($message, 0, $previous);
	}

	protected static function makeMessage(ReflectionParameter $param, ReflectionFunctionAbstract $func = null)
	{
		$pos = $param->getPosition() + 1;

		$name = $param->getName();

		$func = static::getFunctionName($func ?: $param->getDeclaringFunction());

		return "Unresolvable argument: Argument #{$pos} (\${$name}) of {$func}";
	}

	protected static function getFunctionName(ReflectionFunctionAbstract $func)
	{
		if ($func->isClosure()) {
			return 'closure in '.static::getClosureLocation($func);
		}

		if ($func instanceof ReflectionMethod) {
			return $func->getDeclaringClass()->getName() . '::' . $func->getName();
		}

		return $func->getName().' in '.$func->getFileName();
	}

	protected static function getClosureLocation(ReflectionFunctionAbstract $func)
	{
		if ($class = $func->getClosureScopeClass()) {
			$location = $class->getName();
		} else {
			$location = $func->getFileName();
		}

		$startLine = $func->getStartLine();
		$endLine = $func->getEndLine();

		if ($startLine == $endLine) {
			$location .= " on line $startLine";
		} else {
			$location .= " on lines {$startLine}-{$endLine}";
		}

		return $location;
	}
}
