<?php
/**
 * This file is part of the Autarky package.
 *
 * (c) Andreas Lutro <anlutro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Autarky\Container\Factory;

use Autarky\Container\ContainerInterface;
use Autarky\Container\Exception\NotInstantiableException;
use Autarky\Container\Exception\UnresolvableArgumentException;
use Closure;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;

/**
 * Factory definition.
 */
class Definition implements FactoryInterface
{
	/**
	 * The callable function/method.
	 *
	 * @var callable
	 */
	protected $callable;

	/**
	 * The name of the definition.
	 *
	 * @var string|null
	 */
	protected $name;

	/**
	 * Map of argument position => argument
	 *
	 * @var array
	 */
	protected $argumentPositions = [];

	/**
	 * Map of argument name => argument
	 *
	 * @var array
	 */
	protected $argumentNames = [];

	/**
	 * Map of argument class => argument
	 *
	 * @var array
	 */
	protected $argumentClasses = [];

	/**
	 * The current argument postition.
	 *
	 * @var integer
	 */
	protected $argumentPosition = 0;

	/**
	 * Constructor.
	 *
	 * @param callable $callable
	 */
	public function __construct($callable, $name = null)
	{
		$this->callable = $callable;
		$this->name = $name;
	}

	/**
	 * Get a default factory for a class.
	 *
	 * @param  string $class
	 * @param  array  $params Optional
	 *
	 * @return FactoryInterface
	 */
	public static function getDefaultForClass($class, array $params = array())
	{
		$reflectionClass = new ReflectionClass($class);

		if (!$reflectionClass->isInstantiable()) {
			throw new NotInstantiableException("Class $class is not instantiable");
		}

		$factory = new static([$reflectionClass, 'newInstance'], "$class::__construct");

		if ($reflectionClass->hasMethod('__construct')) {
			static::addReflectionArguments($factory, $reflectionClass->getMethod('__construct'));
		}

		return $factory->getFactory($params);
	}

	/**
	 * Get a default factory for a callable.
	 *
	 * @param  callable $callable
	 * @param  array    $params   Optional
	 *
	 * @return FactoryInterface
	 */
	public static function getDefaultForCallable($callable, array $params = array())
	{
		if ($callable instanceof Closure) {
			$factory = new static($callable, 'closure');
			$factory->addOptionalClassArgument('$container', 'Autarky\Container\ContainerInterface');
			return $factory->getFactory($params);
		}

		return static::getFromReflection($callable, null)
			->getFactory($params);
	}

	/**
	 * Get a new factory definition instance via reflection.
	 *
	 * @param  callable                   $callable
	 * @param  ReflectionFunctionAbstract $reflectionFunction
	 *
	 * @return static
	 */
	public static function getFromReflection($callable, ReflectionFunctionAbstract $reflectionFunction = null)
	{
		$factory = new static($callable);

		static::addReflectionArguments($factory, $reflectionFunction);

		return $factory;
	}

	/**
	 * Add arguments to an existing factory via reflection.
	 *
	 * @param Definition                      $factory
	 * @param ReflectionFunctionAbstract|null $reflectionFunction Optional
	 */
	protected static function addReflectionArguments(Definition $factory, ReflectionFunctionAbstract $reflectionFunction = null)
	{
		if (!$reflectionFunction) {
			$callable = $factory->getCallable();

			if (is_array($callable)) {
				$reflectionFunction = new ReflectionMethod($callable[0], $callable[1]);
			} else {
				$reflectionFunction = new ReflectionFunction($callable);
			}
		}

		foreach ($reflectionFunction->getParameters() as $arg) {
			try {
				$name = $arg->getName();
				$required = ! $arg->isOptional();
				if ($argClass = $arg->getClass()) {
					$factory->addClassArgument($name, $argClass->getName(), $required);
				} else {
					$default = ($required ? null : $arg->getDefaultValue());
					$factory->addScalarArgument($name, null, $required, $default);
				}
			} catch (ReflectionException $re) {
				throw UnresolvableArgumentException::fromReflectionParam(
					$arg, $reflectionFunction, $re);
			}
		}
	}

	/**
	 * Get the factory's name.
	 *
	 * @return string|null
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Get the factory callable.
	 *
	 * @return callable
	 */
	public function getCallable()
	{
		return $this->callable;
	}

	/**
	 * Get the factory definition's arguments, mapped by position.
	 *
	 * @return array
	 */
	public function getArguments()
	{
		return $this->argumentPositions;
	}

	/**
	 * Add a scalar argument to the factory definition.
	 *
	 * @param string  $name
	 * @param string  $type     int, string, object, etc.
	 * @param boolean $required
	 * @param mixed   $default  Default value, if not required
	 */
	public function addScalarArgument($name, $type, $required = true, $default = null)
	{
		return $this->addArgument(new ScalarArgument($this->argumentPosition++, $name, $type, $required, $default));
	}

	/**
	 * Add an optional scalar argument to the factory definition.
	 *
	 * @param string  $name
	 * @param string  $type    int, string, object, etc.
	 * @param mixed   $default
	 */
	public function addOptionalScalarArgument($name, $type, $default)
	{
		return $this->addArgument(new ScalarArgument($this->argumentPosition++, $name, $type, false, $default));
	}

	/**
	 * Add a class argument to the factory definition.
	 *
	 * @param string  $name
	 * @param string  $class
	 * @param boolean $required Optional - defaults to true
	 */
	public function addClassArgument($name, $class, $required = true)
	{
		return $this->addArgument(new ClassArgument($this->argumentPosition++, $name, $class, $required));
	}

	/**
	 * Add an optional class argument to the factory definition.
	 *
	 * @param string  $name
	 * @param string  $class
	 */
	public function addOptionalClassArgument($name, $class)
	{
		return $this->addArgument(new ClassArgument($this->argumentPosition++, $name, $class, false));
	}

	/**
	 * Add an argument to the factory definition.
	 *
	 * @param ArgumentInterface $argument
	 */
	public function addArgument(ArgumentInterface $argument)
	{
		$this->argumentPositions[$argument->getPosition()] = $argument;
		$this->argumentNames[$argument->getName()] = $argument;
		if ($argument->isClass()) {
			$this->argumentClasses[$argument->getClass()] = $argument;
		}
		return $argument;
	}

	/**
	 * Get the definition's factory.
	 *
	 * @param  array  $params
	 *
	 * @return Factory
	 */
	public function getFactory(array $params = array())
	{
		return new Factory($this, $params);
	}

	/**
	 * {@inheritdoc}
	 */
	public function invoke(ContainerInterface $container, array $params = array())
	{
		return $this->getFactory($params)
			->invoke($container);
	}
}
