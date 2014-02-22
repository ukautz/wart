<?php
/**
 * This class is part of PimpleOh
 */

namespace Wart\Tests\Fixtures;


/**
 * Class Arg
 * @package Wart\Tests\Fixtures
 **/
class Arg
{
    protected $arg;

    public function __construct($arg = null)
    {
        $this->arg = $arg;
    }

    public function getArg()
    {
        return $this->arg;
    }

}