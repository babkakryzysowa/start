<?php

namespace tests\AppBundle\Service;


//use PHPUnit\Framework\TestCase;

use AppBundle\Service\DateService;

class TestDateService extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function testGetDay()
    {
        $dateService = new DateService();

        $this->assertEquals(19, $dateService->getDay(new \DateTime("2017-01-29")), "Powinien być zwrócony dzień 19");
        $this->assertEquals(1, $dateService->getDay(new \DateTime("2018-01-03")));
    }
}
