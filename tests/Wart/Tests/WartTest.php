<?php
/**
 * This class is part of Wart
 */

namespace Wart\Tests;

use PHPUnit\Framework\TestCase;
use Wart\Tests\Fixtures;

/**
 * Class WartTest
 * @package Wart\Tests
 **/
class WartTest extends TestCase
{
    protected function tearDown()
    {
        Fixtures\Bar::$INSTANCE_CREATE_COUNTER = 0;
        parent::tearDown();
    }

    public function testCreateInstance()
    {
        new \Wart();
        $this->assertTrue(true);
    }

    public function testCreateClassInstanceWithoutConstructor()
    {
        $wart = new \Wart();
        $foo = $wart->create('\Wart\Tests\Fixtures\Foo');
        $this->assertInstanceOf('\Wart\Tests\Fixtures\Foo', $foo);
    }

    public function testAccessCreatedClassInstance()
    {
        $wart  = new \Wart();
        $created = $wart->create('\Wart\Tests\Fixtures\Foo');
        $this->assertSame($created, $wart['\Wart\Tests\Fixtures\Foo']);
    }

    public function testCreateClassInstanceWithAutoResolvedConstructor()
    {
        $wart = new \Wart();
        $wart->create('\Wart\Tests\Fixtures\Bar');
        $this->assertInstanceOf('\Wart\Tests\Fixtures\Foo', $wart['\Wart\Tests\Fixtures\Bar']->getFoo());
    }

    public function testCreateClassInstanceWithAdditionalCreateArgs()
    {
        $wart = new \Wart(array(), array(
            'createArgs' => array(
                '\Wart\Tests\Fixtures\Baz' => array('bla')
            )
        ));
        $wart->create('\Wart\Tests\Fixtures\Baz');
        $this->assertInstanceOf('\Wart\Tests\Fixtures\Foo', $wart['\Wart\Tests\Fixtures\Baz']->getFoo());
        $this->assertInstanceOf('\Wart\Tests\Fixtures\Bar', $wart['\Wart\Tests\Fixtures\Baz']->getBar());
        $this->assertSame('bla', $wart['\Wart\Tests\Fixtures\Baz']->getBaz());
    }

    public function testCreateClassInstanceWithCustomMergeFunction()
    {
        $wart = new \Wart(array(), array(
            'createArgs' => array(
                '\Wart\Tests\Fixtures\Baz' => function (array $buildArgs, $className, \Wart $p) {
                        return array_merge($buildArgs, array('bla'));
                    }
            )
        ));
        $wart->create('\Wart\Tests\Fixtures\Baz');
        $this->assertInstanceOf('\Wart\Tests\Fixtures\Foo', $wart['\Wart\Tests\Fixtures\Baz']->getFoo());
        $this->assertInstanceOf('\Wart\Tests\Fixtures\Bar', $wart['\Wart\Tests\Fixtures\Baz']->getBar());
        $this->assertSame('bla', $wart['\Wart\Tests\Fixtures\Baz']->getBaz());
    }

    /**
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessageRegExp /Invalid create args .* Use either array or Callable/
     */
    public function testFailCreateClassInstanceWithInvalidCreateArgs()
    {
        $wart = new \Wart(array(), array(
            'createArgs' => array(
                '\Wart\Tests\Fixtures\Baz' => 'bla'
            )
        ));
        $wart->create('\Wart\Tests\Fixtures\Baz');
        $this->assertInstanceOf('\Wart\Tests\Fixtures\Foo', $wart['\Wart\Tests\Fixtures\Baz']->getFoo());
        $this->assertInstanceOf('\Wart\Tests\Fixtures\Bar', $wart['\Wart\Tests\Fixtures\Baz']->getBar());
        $this->assertSame('bla', $wart['\Wart\Tests\Fixtures\Baz']->getBaz());
    }

    public function testAutoCreateClassInstance()
    {
        $wart = new \Wart();
        $this->assertInstanceOf('\Wart\Tests\Fixtures\Foo', $wart['\Wart\Tests\Fixtures\Foo']);
    }

    public function testClassResolvingWithNamespaces()
    {
        $wart = new \Wart(array(), array(
            'namespaces' => array(
                '\Wart\Tests',
                '\Wart\Tests\Fixtures',
            )
        ));
        $this->assertInstanceOf('\Wart\Tests\Fixtures\Foo', $wart['Foo']);
        $this->assertInstanceOf('\Wart\Tests\Fixtures\Bar', $wart['Fixtures\Bar']);
    }

    public function testClassPreRegistering()
    {
        $wart = new \Wart();
        $wart->autoRegister('\Wart\Tests\Fixtures\Foo');
        $wart->autoRegister('\Wart\Tests\Fixtures\Bar');
        $this->assertInstanceOf('\Wart\Tests\Fixtures\Foo', $wart['\Wart\Tests\Fixtures\Foo']);
        $this->assertInstanceOf('\Wart\Tests\Fixtures\Bar', $wart['\Wart\Tests\Fixtures\Bar']);
    }

    public function testClassPreRegisteringWithArgs()
    {
        $wart = new \Wart(array(), array(
            'createArgs' => array(
                '\Wart\Tests\Fixtures\Baz' => array('bla')
            )
        ));
        $wart->autoRegister('\Wart\Tests\Fixtures\Baz');
        $wart->autoRegister('\Wart\Tests\Fixtures\Bar');
        $this->assertInstanceOf('\Wart\Tests\Fixtures\Baz', $wart['\Wart\Tests\Fixtures\Baz']);
        $this->assertInstanceOf('\Wart\Tests\Fixtures\Bar', $wart['\Wart\Tests\Fixtures\Baz']->getBar());
        $this->assertSame('bla', $wart['\Wart\Tests\Fixtures\Baz']->getBaz());
    }

    public function testCreateDoesInstantiate()
    {
        $wart = new \Wart();
        $this->assertSame(0, Fixtures\Bar::$INSTANCE_CREATE_COUNTER);
        $wart->create('\Wart\Tests\Fixtures\Bar');
        $this->assertSame(1, Fixtures\Bar::$INSTANCE_CREATE_COUNTER);
    }

    public function testRegisterDoesNotInstantiate()
    {
        $wart = new \Wart();
        $this->assertSame(0, Fixtures\Bar::$INSTANCE_CREATE_COUNTER);
        $wart->autoRegister('\Wart\Tests\Fixtures\Bar');
        $this->assertSame(0, Fixtures\Bar::$INSTANCE_CREATE_COUNTER);
    }

    /**
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage Identifier "\Wart\Tests\Fixtures\Nada" is not defined.
     */
    public function testDoNotAutoResolveNonExistingClass()
    {
        $wart = new \Wart();
        $nada   = $wart['\Wart\Tests\Fixtures\Nada'];
    }

    /**
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage Class "\Wart\Tests\Fixtures\Nada" does not exist and could not be found in namespaces
     */
    public function testCreateThrowsExceptionOnNotExistingClass()
    {
        $wart = new \Wart();
        $wart->create('\Wart\Tests\Fixtures\Nada');
    }

    /**
     * @expectedException        \RuntimeException
     * @expectedExceptionMessage Found circular dependencies: \Wart\Tests\Fixtures\Cycle1 => \Wart\Tests\Fixtures\Cycle2 => \Wart\Tests\Fixtures\Cycle3 => \Wart\Tests\Fixtures\Cycle1
     */
    public function testCircularDependenciesAreRecognized()
    {
        $wart = new \Wart();
        $wart->create('\Wart\Tests\Fixtures\Cycle1');
    }

    /**
     * @expectedException        \RuntimeException
     * @expectedExceptionMessage Found circular dependencies: \Wart\Tests\Fixtures\Cycle1 => \Wart\Tests\Fixtures\Cycle2 => \Wart\Tests\Fixtures\Cycle3 => \Wart\Tests\Fixtures\Cycle1
     */
    public function testCircularDependenciesAreRecognizedWithArrayAccess()
    {
        $wart = new \Wart();
        $foo    = $wart['\Wart\Tests\Fixtures\Cycle1'];
    }

    /**
     * @expectedException        \RuntimeException
     * @expectedExceptionMessage Found circular dependencies: \Wart\Tests\Fixtures\Cycle1 => \Wart\Tests\Fixtures\Cycle2 => \Wart\Tests\Fixtures\Cycle3 => \Wart\Tests\Fixtures\Cycle1
     */
    public function testCircularDependenciesAreRecognizedOnSecondRun()
    {
        $wart           = new \Wart();
        $exceptionMessage = '';
        try {
            $foo = $wart['\Wart\Tests\Fixtures\Cycle1'];
        } catch (\RuntimeException $e) {
            $exceptionMessage = $e->getMessage();
        }
        $this->assertContains('Found circular dependencies', $exceptionMessage);
        $foo = $wart['\Wart\Tests\Fixtures\Cycle1'];
    }

    public function testCircularDependenciesAreResetted()
    {
        $wart           = new \Wart();
        $exceptionMessage = '';
        try {
            $foo = $wart['\Wart\Tests\Fixtures\Cycle1'];
        } catch (\RuntimeException $e) {
            $exceptionMessage = $e->getMessage();
        }
        $this->assertContains('Found circular dependencies', $exceptionMessage);

        unset($wart['\Wart\Tests\Fixtures\Cycle1']);
        $c1                                        = \Mockery::mock('\Wart\Tests\Fixtures\Cycle1');
        $wart['\Wart\Tests\Fixtures\Cycle1'] = $c1;
        $foo                                       = $wart['\Wart\Tests\Fixtures\Cycle1'];
        $this->assertSame($c1, $foo);
        $this->assertArrayHasKey('\Wart\Tests\Fixtures\Cycle2', $wart);
        $this->assertArrayHasKey('\Wart\Tests\Fixtures\Cycle3', $wart);
    }

    public function testCircularFreeDoesNotThrowException()
    {
        $wart = new \Wart();
        $wart->create('\Wart\Tests\Fixtures\NoCycle1');
        $this->assertTrue(true);
    }

    public function testAvertedCircularDependencyThrowsNoException()
    {
        $wart                                    = new \Wart();
        $c1                                        = \Mockery::mock('\Wart\Tests\Fixtures\Cycle1');
        $wart['\Wart\Tests\Fixtures\Cycle1'] = $c1;
        $wart->create('\Wart\Tests\Fixtures\Cycle2');
        $this->assertTrue(true);
    }

    public function testTwoCreationsDoNotTriggerCircularDependencyException()
    {
        $wart = new \Wart();
        $foo = $wart->create('\Wart\Tests\Fixtures\Foo');
        unset($wart['\Wart\Tests\Fixtures\Foo']);
        $newFoo = $wart->create('\Wart\Tests\Fixtures\Foo');
        $this->assertInstanceOf('\Wart\Tests\Fixtures\Foo', $foo);
        $this->assertInstanceOf('\Wart\Tests\Fixtures\Foo', $newFoo);
    }

    /**
     * @expectedException        \RuntimeException
     * @expectedExceptionMessage Cannot override frozen service "\Wart\Tests\Fixtures\Foo"
     */
    public function testMultiCreationMustNotBeAllowed()
    {
        $wart = new \Wart();
        $wart->create('\Wart\Tests\Fixtures\Foo');
        $wart->create('\Wart\Tests\Fixtures\Foo');
    }

    /**
     * @expectedException        \RuntimeException
     * @expectedExceptionMessage Cannot override frozen service "Foo"
     */
    public function testMultiCreationMustNotBeAllowedWithNamespaces()
    {
        $wart = new \Wart(array(), array(
            'namespaces' => array(
                '\Wart\Tests\Fixtures'
            )
        ));
        $wart['Foo'] = 'Bar';
        $wart->create('Foo');
    }

    public function testSetNamespacesPostHoc()
    {
        $wart = new \Wart();
        $exceptionMessage = '';
        try {
            $wart->create('Foo');
        } catch (\InvalidArgumentException $e) {
            $exceptionMessage = $e->getMessage();
        }
        $this->assertSame('Class "Foo" does not exist and could not be found in namespaces', $exceptionMessage);
        $wart->setNamespaces(array('\Wart\Tests\Fixtures'));
        $foo = $wart->create('Foo');
        $this->assertInstanceOf('\Wart\Tests\Fixtures\Foo', $foo);
    }

    public function testSetAutoResolvePostHoc()
    {
        $wart = new \Wart();
        $foo = $wart['\Wart\Tests\Fixtures\Foo'];
        $this->assertInstanceOf('\Wart\Tests\Fixtures\Foo', $foo);
        $wart->setAutoResolve(false);
        $exceptionMessage = '';
        try {
            $bar = $wart['\Wart\Tests\Fixtures\Bar'];
        } catch (\InvalidArgumentException $e) {
            $exceptionMessage = $e->getMessage();
        }
        $this->assertSame('Identifier "\Wart\Tests\Fixtures\Bar" is not defined.', $exceptionMessage);
        $wart->setAutoResolve(true);
        $bar = $wart['\Wart\Tests\Fixtures\Bar'];
        $this->assertInstanceOf('\Wart\Tests\Fixtures\Bar', $bar);
    }

    public function testSetCreateArgsPostHoc()
    {
        $wart = new \Wart();
        $arg = $wart['\Wart\Tests\Fixtures\Arg'];
        $this->assertInstanceOf('\Wart\Tests\Fixtures\Arg', $arg);
        $this->assertNull($arg->getArg());

        unset($wart['\Wart\Tests\Fixtures\Arg']);
        $wart->setCreateArgs(array(
            '\Wart\Tests\Fixtures\Arg' => array('ARG')
        ));
        $arg = $wart['\Wart\Tests\Fixtures\Arg'];
        $this->assertInstanceOf('\Wart\Tests\Fixtures\Arg', $arg);
        $this->assertSame('ARG', $arg->getArg());
    }

    public function testSetAliasesWithInterface()
    {
        $wart = new \Wart();
        $wart->setAliases(['\Wart\Tests\Fixtures\FooInterface' => '\Wart\Tests\Fixtures\Foo']);
        $this->assertInstanceOf('\Wart\Tests\Fixtures\Foo', $wart['\Wart\Tests\Fixtures\FooInterface']);
    }
}
