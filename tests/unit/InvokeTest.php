<?php

use Autarky\Container\Container;

class InvokeTest extends PHPUnit_Framework_TestCase
{
	protected function makeContainer()
	{
		return new Container;
	}

	/** @test */
	public function invokeCanInvokeClosure()
	{
		$c = $this->makeContainer();
		$retval = $c->invoke(function() { return 42; });
		$this->assertEquals(42, $retval);
	}

	/** @test */
	public function invokeCanInvokeObjectMethod()
	{
		$c = $this->makeContainer();
		$retval = $c->invoke([new StubFactory, 'makeFoo']);
		$this->assertEquals('foo', $retval);
	}

	/** @test */
	public function invokeThrowsExceptionOnUnresolvableArgument()
	{
		$this->setExpectedException('Autarky\Container\Exception\UnresolvableArgumentException',
			'Unresolvable argument: Argument #1 ($foo) of StaticStub::f');
		$c = $this->makeContainer();
		$c->invoke(['StaticStub', 'f']);
	}

	/** @test */
	public function invokeExceptionMessageIsCorrectForClosures()
	{
		$this->setExpectedException('Autarky\Container\Exception\UnresolvableArgumentException',
			'Unresolvable argument: Argument #1 ($foo) of closure in '.__CLASS__.' on line');
		$c = $this->makeContainer();
		$c->invoke(function($foo){});
	}

	/** @test */
	public function invokeCanInvokeStaticMethods()
	{
		$c = $this->makeContainer();
		$retval = $c->invoke(['StaticStub', 'f'], ['$foo' => 'foo']);
		$this->assertEquals('foobar', $retval);
	}

	/** @test */
	public function invokeCanInvokeStaticMethodsWithString()
	{
		$c = $this->makeContainer();
		$retval = $c->invoke('StaticStub::f', ['$foo' => 'foo']);
		$this->assertEquals('foobar', $retval);
	}

	/** @test */
	public function invokeResolvesDependencies()
	{
		$c = $this->makeContainer();
		$callback = function(LowerClass $lc) { return $lc; };
		$retval = $c->invoke($callback);
		$this->assertInstanceOf('LowerClass', $retval);
	}

	/** @test */
	public function invokeCanBePassedParams()
	{
		$c = $this->makeContainer();
		$callback = function($param) { return $param; };
		$retval = $c->invoke($callback, ['$param' => 42]);
		$this->assertEquals(42, $retval);
	}

	/** @test */
	public function invokeCanBePassedObjectParam()
	{
		$c = $this->makeContainer();
		$lc = new LowerClass;
		$callback = function(LowerClass $lc) { return $lc; };
		$retval = $c->invoke($callback, ['$lc' => $lc]);
		$this->assertSame($lc, $retval);
	}

	/** @test */
	public function invokeDoesNotGetParamsForClassConstructor()
	{
		$c = $this->makeContainer();
		$c->params('ParamConflictStub', ['$value' => 'foo']);
		$this->assertEquals('default', $c->invoke(['ParamConflictStub', 'doStuff']));
		$this->assertEquals('bar', $c->invoke(['ParamConflictStub', 'doStuff'], ['$value' => 'bar']));
	}
}
