<?php
/**
 * This class is part of Wart
 */

namespace Wart\Tests\Fixtures;


/**
 * Class Cycle3
 * @package Wart\Tests\Fixtures
 **/
class Cycle3
{

    public function __construct(Cycle1 $c2)
    {

    }

}