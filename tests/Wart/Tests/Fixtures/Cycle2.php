<?php
/**
 * This class is part of Wart
 */

namespace Wart\Tests\Fixtures;


/**
 * Class Cycle2
 * @package Wart\Tests\Fixtures
 **/
class Cycle2
{

    public function __construct(Cycle3 $c3)
    {

    }

}