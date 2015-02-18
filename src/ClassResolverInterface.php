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
 * Class resolver interface.
 *
 * Type-hint against this class if you only require to resolve classes from the
 * container, not define factories, add callbacks or any of the other
 * functionality. The ContainerInterface extends this interface, so if you type-
 * hint against this interface, you will still get the container in most cases.
 */
interface ClassResolverInterface
{
	/**
	 * Resolve a class from the container. Dependencies of the resolved
	 * object will be resolved recursively.
	 *
	 * @param  string $class
	 *
	 * @return mixed
	 */
	public function resolve($class);
}
