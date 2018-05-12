<?php
/**
 * Created by PhpStorm.
 * User: janwaldecker
 * Date: 24.03.18
 * Time: 19:07
 */

namespace Unit;


use NicAPI\DateTimeMigrator;
use NicAPI\NicAPI;
use PHPUnit\Framework\TestCase;

class MigratorTest extends TestCase
{

    public function testDateTimeMigration()
    {
        $array = [
            'time' => new \DateTime('2016-01-01'),
            'sub' => [
                'time' => new \DateTime('2018-01-01')
            ]
        ];

        $result = DateTimeMigrator::formatValues($array);

        $this->assertEquals('2016-01-01 00:00:00', $result['time']);
        $this->assertEquals('2018-01-01 00:00:00', $result['sub']['time']);
    }

}