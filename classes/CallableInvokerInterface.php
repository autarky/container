<?php
/**
 * This file is part of the Autarky package.
 *
 * (c) Andreas Lutro <anlutro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Autarky\Container;

/**
 * Callable invoker interface.
 *
 * Type-hint against this interface if you only require to invoke callables via
 * the container. The ContainerInterface extends this interface.
 */
interface CallableInvokerInterface
{
	/**
	 * Execute a function, closure or class method, resolving type-hinted
	 * arguments as necessary.
	 *
	 * Callable can be anything that passes is_callable() in PHP, including an
	 * array of ['ClassName', 'method'], in which case the class will first be
	 * resolved from the container. Callable can also be some things that don't
	 * pass is_callable(), for example ['InterfaceName', 'method'], but only if
	 * 'InterfaceName' is bound to the container somehow.
	 *
	 * @param  callable $callable
	 * @param  array    $params   See ContainerInterface::params()
	 *
	 * @return mixed
	 */
	public function invoke($callable, array $params = array());
}
