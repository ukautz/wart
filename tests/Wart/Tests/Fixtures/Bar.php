<?php
/**
 * This class is part of Wart
 */

namespace Wart\Tests\Fixtures;


/**
 * Class Bar
 * @package Wart\Tests\Fixtures
 **/
class Bar
{
    public static $INSTANCE_CREATE_COUNTER = 0;
    protected $foo;

    public function __construct(Foo $foo)
    {
        static::$INSTANCE_CREATE_COUNTER++;
        $this->foo = $foo;
    }

    public function getFoo()
    {
        return $this->foo;
    }


}