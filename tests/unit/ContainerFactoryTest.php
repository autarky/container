<?php

use Mockery as m;
use Autarky\Container\ContainerInterface;
use Autarky\Container\Container;

class ContainerFactoryTest extends PHPUnit_Framework_TestCase
{
	protected function tearDown()
	{
		m::close();
	}

	protected function makeContainer()
	{
		return new Container;
	}

	/** @test */
	public function factoriesAreNotSharedByDefault()
	{
		$c = $this->makeContainer();
		$c->define('foo', function() { return new \StdClass; });
		$this->assertNotSame($c->resolve('foo'), $c->resolve('foo'));
	}

	/** @test */
	public function defineWithResolvableFactoryArray()
	{
		$c = $this->makeContainer();
		$c->define('foo', ['StubFactory', 'makeFoo']);
		$this->assertEquals('foo', $c->resolve('foo'));
	}

	/** @test */
	public function defineWithResolvableFactoryArrayWithAliasedInterface()
	{
		$c = $this->makeContainer();
		$c->define('foo', ['StubFactoryInterface', 'makeFoo']);
		$c->alias('StubFactory', 'StubFactoryInterface');
		$this->assertEquals('foo', $c->resolve('foo'));
	}

	/** @test */
	public function multipleParamCallsAddUp()
	{
		$c = $this->makeContainer();
		$c->params('ParamStub', ['$foo' => 'old_foo']);
		$c->params('ParamStub', ['$foo' => 'new_foo', '$bar' => 'bar']);
		$o = $c->resolve('ParamStub');
		$this->assertEquals('new_foo', $o->foo);
		$this->assertEquals('bar', $o->bar);
	}

	/** @test */
	public function isBoundReturnsTrueWhenBound()
	{
		$c = $this->makeContainer();

		$this->assertEquals(false, $c->isBound('foo'));
		$c->define('foo', function() { return 'foo'; });
		$this->assertEquals(true, $c->isBound('foo'));
	}


	/** @test */
	public function canDefineDynamicFactoryParam()
	{
		$c = $this->makeContainer();
		$f = $c->makeFactory(function($variable) {
			return strtoupper($variable);
		});
		$f->addScalarArgument('$variable', 'string');
		$c->define('var.service', $f);
		$c->params('ParamStub', [
			'$foo' => $c->getFactory('var.service', ['$variable' => 'foo']),
			'$bar' => $c->getFactory('var.service', ['$variable' => 'bar']),
		]);
		$obj = $c->resolve('ParamStub');
		$this->assertEquals('FOO', $obj->foo);
		$this->assertEquals('BAR', $obj->bar);
	}

	/** @test */
	public function canDefineNonFactoryArrayAsParameter()
	{
		$c = $this->makeContainer();
		$c->params('ParamStub', [
			'$foo' => ['foo', 'bar', 'baz'],
			'$bar' => ['baz', 'bar', 'foo'],
		]);
		$obj = $c->resolve('ParamStub');
		$this->assertEquals(['foo', 'bar', 'baz'], $obj->foo);
		$this->assertEquals(['baz', 'bar', 'foo'], $obj->bar);
	}

	/** @test */
	public function canDefineDynamicFactoryParamWithClasses()
	{
		$c = $this->makeContainer();
		$f = $c->makeFactory(function($value) {
			return new ValueLowerClass($value);
		});
		$f->addScalarArgument('$value', 'mixed');
		$c->define('ValueLowerClass', $f);
		$c->params('UpperClass', [
			'LowerClass' => $c->getFactory('ValueLowerClass', ['$value' => 'foobar']),
		]);
		$obj = $c->resolve('UpperClass');
		$this->assertInstanceOf('ValueLowerClass', $obj->cl);
		$this->assertEquals('foobar', $obj->cl->value);
	}
}

