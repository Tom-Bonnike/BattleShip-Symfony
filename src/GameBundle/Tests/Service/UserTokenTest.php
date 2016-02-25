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

    public function testValidate(){
        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\HttpException');
        $this->userToken->validate(null);
    }
}