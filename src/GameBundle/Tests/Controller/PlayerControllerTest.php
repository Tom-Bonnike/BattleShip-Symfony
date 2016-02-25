<?php

namespace GameBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use GameBundle\Repository\PlayerRepository;

class PlayerControllerTest extends WebTestCase
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

    public function testShowPlayerAction()
    {
        $route = $this->router->generate('show_player', array('id' => 1));
        $this->client->request('GET', $route);

        $response = $this->client->getResponse();
        $this->assertJsonResponse($response);

        $content = $response->getContent();
        $decodedPlayer = json_decode($content, true);

        // Test if the user token is hidden (to avoid spoofing)
        $this->assertFalse(isset($decodedPlayer['token']));
    }

    public function testCreatePlayerAction()
    {
        $route = $this->router->generate('create_player');
        $this->client->request('POST', $route, ['name' => 'Test']);

        $response = $this->client->getResponse();

        $this->assertJsonResponse($response, 201);

        // Test if the response has a Location header
        $this->assertTrue($response->headers->has('Location'), $response->headers);

        $content = $response->getContent();
        $decodedPlayer = json_decode($content, true);

        // Test if the response does contain a player, a token and a message.
        $this->assertTrue(isset($decodedPlayer['player']), isset($decodedPlayer['token']), isset($decodedPlayer['message']));
    }

    public function testDeletePlayerAction()
    {
        $player = $this->em->getRepository('GameBundle:Player')->findOneByName('Test');

        $route = $this->router->generate('delete_player', ['id' => $player->getId()]);
        $this->client->request('DELETE', $route, [], [], ['HTTP_X-USER-TOKEN' => $player->getToken()]);

        $response = $this->client->getResponse();

        $this->assertEquals(204, $response->getStatusCode(), $response->getContent());
    }
}