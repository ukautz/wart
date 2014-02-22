<?php
/**
 * This class is part of Wart
 */

namespace Wart\Tests\Fixtures;


/**
 * Class Cycle1
 * @package Wart\Tests\Fixtures
 **/
class Cycle1
{

    public function __construct(Cycle2 $c2)
    {

    }

}