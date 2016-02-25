<?php

namespace GameBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use GameBundle\Repository\RoomRepository;

class RoomControllerTest extends WebTestCase
{
    private $repo;

    protected function setUp()
    {
        self::bootKernel();

        $this->client = static::createClient();

        $this->em = static::$kernel->getContainer()->get('doctrine.orm.entity_manager');

        $this->router = static::$kernel->getContainer()->get('router');
    }

    protected function assertJsonResponse($response, $statusCode = 200) {
        $this->assertEquals($statusCode, $response->getStatusCode(), $response->getContent());
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'), $response->headers);
    }

    public function testListRoomsAction()
    {
        // Test JSON type
        $route = $this->router->generate('list_rooms');
        $this->client->request('GET', $route);

        $response = $this->client->getResponse();

        $this->assertJsonResponse($response);

        // Test responses count
        $er = $this->em->getRepository('GameBundle:Room');
        $count = count($er->findAll());

        $content = $response->getContent();
        $decoded = json_decode($content, true);

        $this->assertCount($count, $decoded);

        // Test XML type
        $route = $this->router->generate('list_rooms', array('_format' => 'xml'));
        $this->client->request('GET', $route);

        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
        $this->assertTrue($response->headers->contains('Content-Type', 'text/xml; charset=UTF-8'), $response->headers);
    }
}