<?php
class LowerClass {}
class ValueLowerClass extends LowerClass {
	public function __construct($value) {
		$this->value = $value;
	}
}
class UpperClass {
	public function __construct(LowerClass $cl) {
		$this->cl = $cl;
	}
}
interface OptionalInterface {}
class OptionalClass implements OptionalInterface {}
class OptionalDependencyClass {
	public function __construct(LowerClass $lc, OptionalInterface $opt = null) {
		$this->lc = $lc; $this->opt = $opt;
	}
}
class ContainerAware implements \Autarky\Container\ContainerAwareInterface
{
	use \Autarky\Container\ContainerAwareTrait;
	public function getContainer()
	{
		return $this->container;
	}
}
class UnresolvableScalarStub {
	public function __construct($value) {}
}
class UnresolvableClassStub {
	public function __construct(ThisClassDoesNotExist $value) {}
}
class DefaultValueStub {
	public $value;
	public function __construct($value = 'foo')
	{
		$this->value = $value;
	}
}
interface StubFactoryInterface {
	public function makeFoo($suffix = '');
}
class StubFactory implements StubFactoryInterface {
	public function makeFoo($suffix = '') {
		return 'foo' . $suffix;
	}
}
class StaticStub {
	public static function f($foo) {
		return $foo.'bar';
	}
}
class ParamStub {
	public function __construct($foo, $bar) {
		$this->foo = $foo;
		$this->bar = $bar;
	}
}
class ParamConflictStub {
	public function __construct($value) {
		$this->value = $value;
	}
	public function doStuff($value = 'default') {
		return $value;
	}
}
