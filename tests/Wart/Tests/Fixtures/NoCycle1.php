<?php
/**
 * This class is part of Wart
 */

namespace Wart\Tests\Fixtures;


/**
 * Class NoCycle
 * @package Wart\Tests\Fixtures
 **/
class NoCycle1
{

    public function __construct(NoCycle2 $nc2)
    {

    }

}