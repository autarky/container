<?php

use Mockery as m;
use Autarky\Container\Container;
use Autarky\Container\Proxy\AbstractProxy;

class ProxyTest extends PHPUnit_Framework_TestCase
{
	public function tearDown()
	{
		m::close();
	}

	/** @test */
	public function proxyIsResolved()
	{
		AbstractProxy::setProxyContainer($container = new Container);
		$container->instance('foo', $mock1 = m::mock());
		$container->instance('baz', $mock2 = m::mock());
		$mock1->shouldReceive('foo')->once()->with('bar')->andReturn('baz');
		$mock2->shouldReceive('baz')->once()->with('bar')->andReturn('foo');
		$this->assertEquals('baz', StubProxy::foo('bar'));
		$this->assertEquals('foo', StubProxyTwo::baz('bar'));
	}
}

class StubProxy extends AbstractProxy
{
	protected static function getProxyContainerKey()
	{
		return 'foo';
	}
}
class StubProxyTwo extends AbstractProxy
{
	protected static function getProxyContainerKey()
	{
		return 'baz';
	}
}
