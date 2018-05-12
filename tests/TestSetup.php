<?php
/**
 * Created by PhpStorm.
 * User: janwaldecker
 * Date: 24.03.18
 * Time: 19:07
 */

namespace Unit;


use NicAPI\NicAPI;
use PHPUnit\Framework\TestCase;

class TestSetup extends TestCase
{

    public function testGetMethod()
    {
        $result = NicAPI::get('account/password-reset/check');

        $this->assertEquals('success', $result->status);
    }

}