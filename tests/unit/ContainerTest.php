<?php

use Autarky\Container\Container;

class ContainerTest extends PHPUnit_Framework_TestCase
{
	protected function makeContainer()
	{
		return new Container;
	}

	/** @test */
	public function implementsInterfaces()
	{
		$c = $this->makeContainer();
		$this->assertInstanceOf('Autarky\Container\ClassResolverInterface', $c);
		$this->assertInstanceOf('Autarky\Container\CallableInvokerInterface', $c);
		$this->assertInstanceOf('Autarky\Container\ContainerInterface', $c);
	}

	/** @test */
	public function setContainerIsCalledOnContainerAwareInterfaceClasses()
	{
		$c = $this->makeContainer();
		$o = $c->resolve('ContainerAware');
		$this->assertSame($c, $o->getContainer());
	}

	/** @test */
	public function nonExistingClassesThrowsException()
	{
		$c = $this->makeContainer();
		$this->setExpectedException('ReflectionException');
		$c->resolve('thisclassdoesnotexist');
	}

	/** @test */
	public function nonInstantiableClassNameThrowsException()
	{
		$c = $this->makeContainer();
		$this->setExpectedException('Autarky\Container\Exception\NotInstantiableException');
		$c->resolve('Iterator');
	}

	/** @test */
	public function resolvingCallbacksAreCalled()
	{
		$c = $this->makeContainer();
		$c->define('foo', function() { return new \StdClass; });
		$c->resolving('foo', function($o, $c) { $o->bar = 'baz'; });
		$this->assertEquals('baz', $c->resolve('foo')->bar);
	}

	/** @test */
	public function resolvingCallbacksAreCalledForAliases()
	{
		$c = $this->makeContainer();
		$c->alias('OptionalClass', 'OptionalInterface');
		$called = false;
		$c->resolving('OptionalInterface', function() use(&$called) {
			$called = true;
		});
		$c->resolve('OptionalInterface');
		$this->assertEquals(true, $called);
	}

	/** @test */
	public function resolvingAnyCallbacksAreCalled()
	{
		$c = $this->makeContainer();
		$c->resolvingAny(function($o, $c) { $o->bar = 'baz'; });
		$this->assertEquals('baz', $c->resolve('StdClass')->bar);
	}

	/** @test */
	public function internalClassesAreProtected()
	{
		$c = $this->makeContainer();
		$c->internal('LowerClass');
		$c->resolve('UpperClass');
		$this->setExpectedException('Autarky\Container\Exception\ResolvingInternalException');
		$c->resolve('LowerClass');
	}

	/** @test */
	public function containerAndContainerInterfaceAreShared()
	{
		$c = $this->makeContainer();
		$this->assertSame($c, $c->resolve('Autarky\Container\Container'));
		$this->assertSame($c, $c->resolve('Autarky\Container\ContainerInterface'));
	}

	/** @test */
	public function canPassClassNamesToNonTypeHintedArgs()
	{
		$c = $this->makeContainer();
		$o = $c->resolve('ValueLowerClass', ['$value' => 'LowerClass']);
		$this->assertInstanceOf('LowerClass', $o->value);
	}
}
