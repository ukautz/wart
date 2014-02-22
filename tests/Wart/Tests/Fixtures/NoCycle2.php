<?php
/**
 * This class is part of Wart
 */

namespace Wart\Tests\Fixtures;


/**
 * Class NoCycle2
 * @package Wart\Tests\Fixtures
 **/
class NoCycle2
{

    public function __construct(NoCycle3 $nc3)
    {

    }

}