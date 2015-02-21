<?php

use Autarky\Container\Container;

class AutowiringTest extends PHPUnit_Framework_TestCase
{
	protected function makeContainer()
	{
		return new Container;
	}

	/** @test */
	public function automaticallyResolvesDependencies()
	{
		$c = $this->makeContainer();
		$o1 = $c->resolve('UpperClass');
		$this->assertInstanceOf('UpperClass', $o1);
		$this->assertInstanceOf('LowerClass', $o1->cl);
		$o2 = $c->resolve('UpperClass');
		$this->assertNotSame($o1, $o2);
		$this->assertNotSame($o1->cl, $o2->cl);
	}

	/** @test */
	public function unresolvableScalarArgumentThrowsException()
	{
		$c = $this->makeContainer();
		$this->setExpectedException('Autarky\Container\Exception\UnresolvableArgumentException',
			'Unresolvable argument: Argument #1 ($value) of UnresolvableScalarStub::__construct - Argument is required and has no value');
		$c->resolve('UnresolvableScalarStub');
	}

	/** @test */
	public function unresolvableClassArgumentThrowsException()
	{
		$c = $this->makeContainer();
		$this->setExpectedException('Autarky\Container\Exception\UnresolvableArgumentException',
			'Unresolvable argument: Argument #1 ($value) of UnresolvableClassStub::__construct - Class ThisClassDoesNotExist does not exist');
		$c->resolve('UnresolvableClassStub');
	}

	/** @test */
	public function automaticallyResolvesDependenciesIncludingSharedInstances()
	{
		$c = $this->makeContainer();
		$c->share('LowerClass');
		$o1 = $c->resolve('UpperClass');
		$this->assertInstanceOf('UpperClass', $o1);
		$this->assertInstanceOf('LowerClass', $o1->cl);
		$o2 = $c->resolve('UpperClass');
		$this->assertNotSame($o1, $o2);
		$this->assertSame($o1->cl, $o2->cl);
	}

	/** @test */
	public function resolveOptionalDependencyIsNullWhenNotConfigured()
	{
		$c = $this->makeContainer();
		$o = $c->resolve('OptionalDependencyClass');
		$this->assertInstanceOf('LowerClass', $o->lc);
		$this->assertNull($o->opt);
	}

	/** @test */
	public function optionalDependenciesAreResolvedWithAlias()
	{
		$c = $this->makeContainer();
		$c->alias('OptionalClass', 'OptionalInterface');
		$o = $c->resolve('OptionalDependencyClass');
		$this->assertInstanceOf('LowerClass', $o->lc);
		$this->assertInstanceOf('OptionalClass', $o->opt);
	}

	/** @test */
	public function optionalDependenciesAreResolvedWithParams()
	{
		$c = $this->makeContainer();
		$c->params('OptionalDependencyClass', [
			'OptionalInterface' => 'OptionalClass',
		]);
		$o = $c->resolve('OptionalDependencyClass');
		$this->assertInstanceOf('LowerClass', $o->lc);
		$this->assertInstanceOf('OptionalClass', $o->opt);
	}

	/** @test */
	public function optionalDependenciesAreResolvedWithParamsUsingVariableNames()
	{
		$c = $this->makeContainer();
		$c->params('OptionalDependencyClass', [
			'$opt' => 'OptionalClass',
		]);
		$o = $c->resolve('OptionalDependencyClass');
		$this->assertInstanceOf('LowerClass', $o->lc);
		$this->assertInstanceOf('OptionalClass', $o->opt);
	}

	/** @test */
	public function resolveWithNonClassDependencyThatHasDefaultValue()
	{
		$c = $this->makeContainer();
		$o = $c->resolve('DefaultValueStub');
		$this->assertEquals('foo', $o->value);
	}

	/** @test */
	public function cannotAutoresolveIfAutowiringIsDisabled()
	{
		$c = $this->makeContainer();
		$c->setAutowire(false);
		$this->setExpectedException('Autarky\Container\Exception\ResolvingException');
		$c->resolve('UpperClass');
	}
}
