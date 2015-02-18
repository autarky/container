<?php

use Autarky\Container\Container;

class SharedInstancesTest extends PHPUnit_Framework_TestCase
{
	protected function makeContainer()
	{
		return new Container;
	}

	/** @test */
	public function defineSharedServiceWithClosure()
	{
		$c = $this->makeContainer();
		$c->define('foo', function() { return new \StdClass; });
		$c->share('foo');
		$this->assertSame($c->resolve('foo'), $c->resolve('foo'));
	}

	/** @test */
	public function defineSharedServiceAndAliasIt()
	{
		$c = $this->makeContainer();
		$c->define('LowerClass', function() { return new LowerClass; });
		$c->share('LowerClass');
		$c->alias('LowerClass', 'foo');
		$this->assertSame($c->resolve('foo'), $c->resolve('LowerClass'));
		$this->assertSame($c->resolve('foo'), $c->resolve('UpperClass')->cl);
	}

	/** @test */
	public function putInstanceOntoContainer()
	{
		$c = $this->makeContainer();
		$c->instance('foo', $o = new \StdClass);
		$this->assertSame($o, $c->resolve('foo'));
		$this->assertSame($c->resolve('foo'), $c->resolve('foo'));
	}

	/** @test */
	public function shareWithoutDefiningFirst()
	{
		$c = $this->makeContainer();
		$c->share('StdClass');
		$this->assertInstanceOf('StdClass', $c->resolve('StdClass'));
		$this->assertSame($c->resolve('StdClass'), $c->resolve('StdClass'));
	}

	/** @test */
	public function isBoundReturnsTrueWhenBound()
	{
		$c = $this->makeContainer();

		$this->assertEquals(false, $c->isBound('StdClass'));
		$c->share('StdClass');
		$this->assertEquals(true, $c->isBound('StdClass'));

		$this->assertEquals(false, $c->isBound('bar'));
		$c->instance('bar', 'bar');
		$this->assertEquals(true, $c->isBound('bar'));
	}
}
