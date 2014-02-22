# Wart

Extends the [Pimple](https://github.com/fabpot/Pimple) dependency injection container and provides auto class resolving, instantiation and (constructor) injection

## Synopsis

``` php
<?php

namespace Foo\Bar;

class Baz
{
    public function hello()
    {
        echo "Hello from baz\n";
    }
}

class Bla
{
    protected $baz;
    public function __construct(Baz $baz)
    {
        $this->baz = $baz;
    }
    public function hello()
    {
        $this->baz->hello();
        echo "Hello from bla\n";
    }
}

$container = new Wart;
$container['\Foo\Bar\Baz']->hello(); # prints "Hello from baz\n"
$container['\Foo\Bar\Bla']->hello(); # prints "Hello from baz\nHello from bla\n"
```

## Installation

```
composer.phar require wart/wart "*"
```

## Auto resolving class instances

Auto resolving is per default enabled. It enables the _magic_ of instantiating a class by merely naming them as a container key:

``` php
<?php

$container['\Foo\Bar\Baz']->hello();
```

Auto resolving can be disabled by either setting the container key manually before fetching it or by constructor parameter

``` php
<?php

// auto resolving only works on non existing keys!
$container = new Wart;
$container['\Foo\Bar\Baz'] = 'bla';
echo $container['\Foo\Bar\Baz']; # still "bla"

// disable on construct
$container = new Wart(array(), array('autoResolve' => false));

// disable later on
$container->setAutoResolve(false);

// this will not work anymore (unless it has been set manually)
echo $container['\Foo\Bar\Baz'];
```

## Additional namespaces

Additional namespaces can be passed to the `Wart` constructor:

``` php
<?php

$container = new Wart(array(), array('namespaces' => array('\Foo', '\Foo\Bar')));
$container['Baz']->hello(); # checks for \Baz and \Foo\Baz and \Foo\Bar\Baz - in that order!
```

Or set (replaced!) later:

``` php
<?php

$container = new Wart;
$container->setNamespaces(array('\Foo', '\Foo\Bar'));
$container['Baz']->hello(); # checks for \Baz and \Foo\Baz and \Foo\Bar\Baz - in that order!
```

Mind that the order matters!

## Creating and registering class instances manually

With auto resolving disabled, the class instances can be pre-generated:

``` php
<?php

$container = new Wart(array(), array('autoResolve' => false));
$container['\Foo\Bar\Baz']->hello(); # would throw and exception -> key does not exist
$instance = $container->create('\Foo\Bar\Baz');
$instance->hello();
$container['\Foo\Bar\Baz']->hello(); # now the key exists
```

The `create` method immediately creates an instance. If you just want to pre-register an instance for later key usage, use `register`:

``` php
<?php

$container = new Wart(array(), array('autoResolve' => false));
$container['\Foo\Bar\Baz']->hello();  # would throw and exception -> key does not exist
$container->register('\Foo\Bar\Baz'); # does not return anything
$container['\Foo\Bar\Baz']->hello();  # now the key exists
```

## Additional constructor parameters

To use additional constructor parameters, you can set them with the `createArgs` parameter or with the `setCreateArgs` method

``` php
<?php

namespace Foo\Bar;

class Baz
{
    protected $param;
    public function __construct($param)
    {
        $this->param = $param;
    }
    public function hello()
    {
        echo "Hello {$this->param}\n";
    }
}

class Bla
{
    protected $param;
    public function __construct(Baz $baz, $param)
    {
        $this->param = $param;
    }
    public function hello()
    {
        echo "Hello {$this->param}\n";
    }
}

class Yadda
{
    protected $scalar;
    public function __construct(Baz $baz, $scalar, Bla $bla)
    {
        $this->scalar = $scalar;
    }
    public function hello()
    {
        echo "Hello {$this->scalar}\n";
        $this->bla->Hello();
    }
}

$container = new Wart(array(), array(
    'createArgs' => array(
        '\Foo\Bar\Baz'   => ['world'],
        '\Foo\Bar\Bla'   => ['you'],
        '\Foo\Bar\Yadda' => function (array $createArgs, $className, \Wart $cnt) {
            // $createArgs contains the constructor parameters Wart has already figured out (i.e. all with class type hints)
            // Must return an array with all constructor args
            if ($buildArgs && $buildArgs[0] instanceof \Foo\Bar\Baz) use ($container) { # Wart found
                return [$buildArgs[0], "!", $cnt['\Foo\Bar\Baz']];
            }
            throw new \Exception("Oh no!");
        }
    )
));
$container->register('\Foo\Bar\Baz');   # does not return anything and does NOT create an object instance just yet
$container['\Foo\Bar\Baz']->hello();    # prints "Hello world\n"
$container['\Foo\Bar\Bla']->hello();    # prints "Hello you\n"
$container['\Foo\Bar\Yadda']->hello();  # prints "Hello !\nHello you\n"
```

You can also set (replace!) the constructor parameters later on with `setCreateArgs`

``` php
<?php

$container = new Wart;
$container->setCreateArgs(array(
    '\Foo\Bar\Baz'   => ['world']
));
```

## Limitations

### Constructor parameter signature order

Mind that `Wart` supports only auto determines constructor args which are type hinted classes.

The following `Wart` tries to resolve and inject:
``` php
<?php

class Foo
{
    public function __construct(Bar $bar) {}
}
```

The following `Wart` cannot does _not_ inject, unless you use the `setCreateArgs` method or the `createArgs` argument (see above).
``` php
<?php

class Foo
{
    public function __construct($bar) {}
}
```

Now if the constructor parameter signature order is "adverse", `Wart` cannot resolve it either. The following will _not_ (automatically) work:

``` php
<?php

class Bla
{
    public function __construct($param, Baz $baz) # << Baz $baz is on second position, Wart gives up on first as it's not typehinted to a class
    {
    }
}
```

### Circular dependencies

`Wart` features a simplistic circular dependency recognition. However it's limited in it's capabilities (especially in the context of constructor parameter order, see above)

The following will be recognized by `Wart` (and a `RuntimeException` is thrown):

``` php
<?php

class Foo {
    public function __construct(Bar $bar) {}
}

class Bar {
    public function __construct(Baz $baz) {}
}

class Baz {
    public function __construct(Foo $foo) {}
}
```

Anything else: no guarantees (patches welcome, unless they greatly increase the complexity).
