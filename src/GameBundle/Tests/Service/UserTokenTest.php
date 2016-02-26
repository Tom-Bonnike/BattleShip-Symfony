<?php

namespace GameBundle\Tests\Service;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserTokenTest extends WebTestCase
{
    public function setUp()
    {
        self::bootKernel();
        $this->userToken = static::$kernel->getContainer()->get('usertoken');
    }

    /**
     * Tests the testValidate service method, testing if an HttpException with status code 404 is received if no userToken is sent to the method
     *
     * @test
     *
     * @return void
    */
    public function testValidate()
    {
        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\HttpException');
        $this->userToken->validate('');
    }
}