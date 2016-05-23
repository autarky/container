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

use Autarky\Providers\AbstractProvider;

/**
 * Provides the application with a container.
 *
 * This service provider is vital to the framework.
 */
class ContainerProvider extends AbstractProvider
{
	/**
	 * {@inheritdoc}
	 */
	public function register()
	{
		$this->app->setContainer($dic = new Container);
		$dic->instance('Autarky\Application', $this->app);
	}
}
