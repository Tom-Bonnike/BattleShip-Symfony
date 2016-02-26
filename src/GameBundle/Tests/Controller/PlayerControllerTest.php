<?php

namespace GameBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use GameBundle\Repository\PlayerRepository;

/**
 * Tests for the PlayerController
*/
class PlayerControllerTest extends WebTestCase
{
    private static $_randomName;

    /**
     * Setup function for the test
     *
     * @return void
    */
    protected function setUp()
    {
        self::bootKernel();

        $this->client = static::createClient();

        $this->em = static::$kernel->getContainer()->get('doctrine.orm.entity_manager');

        $this->router = static::$kernel->getContainer()->get('router');
    }

    /**
     * Custom assert to test if a JSON Response has the right headers and if the expected status code is received
     *
     * @param string $response   The response that was received, as a string
     * @param int    $statusCode The status code expected in the response
     *
     *
     * @return void
    */
    protected function assertJsonResponse($response, $statusCode = 200)
    {
        $this->assertEquals($statusCode, $response->getStatusCode(), $response->getContent());
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'), $response->headers);
    }

    /**
     * Tests the ShowPlayerAction, testing if the JSON Response is correct and if the user token is hidden as intended (to avoid spoofing)
     *
     * @test
     *
     * @return void
    */
    public function testShowPlayerAction()
    {
        $route = $this->router->generate('show_player', array('id' => 1));
        $this->client->request('GET', $route);

        $response = $this->client->getResponse();
        $this->assertJsonResponse($response);

        $content = $response->getContent();
        $decodedPlayer = json_decode($content, true);

        $this->assertFalse(isset($decodedPlayer['token']));
    }

    /**
     * Tests the CreatePlayerAction, testing if the JSON Response is correct, if the response has a Location Header and if the received object matches what is expected
     *
     * @test
     *
     * @return void
    */
    public function testCreatePlayerAction()
    {
        $route = $this->router->generate('create_player');

        $randomName = 'TEST-'.uniqid();
        static::$_randomName = $randomName;

        $this->client->request('POST', $route, ['name' => $randomName]);

        $response = $this->client->getResponse();

        $this->assertJsonResponse($response, 201);

        $this->assertTrue($response->headers->has('Location'), $response->headers);

        $content = $response->getContent();
        $decodedPlayer = json_decode($content, true);

        $this->assertTrue(isset($decodedPlayer['player']), isset($decodedPlayer['token']), isset($decodedPlayer['message']));
    }

    /**
     * Tests the DeletePlayerAction, testing if the response status matches the expected status code of 204 and if the user has been deleted
     *
     * @test
     *
     * @return void
    */
    public function testDeletePlayerAction()
    {
        $er     = $this->em->getRepository('GameBundle:Player');
        $player = $er->findOneByName(static::$_randomName);

        $route = $this->router->generate('delete_player', ['id' => $player->getId()]);
        $this->client->request('DELETE', $route, [], [], ['HTTP_X-USER-TOKEN' => $player->getToken()]);

        $response = $this->client->getResponse();

        $this->assertEquals(204, $response->getStatusCode(), $response->getContent());

        $this->assertEquals(null, $er->findOneByName(static::$_randomName));
    }
}