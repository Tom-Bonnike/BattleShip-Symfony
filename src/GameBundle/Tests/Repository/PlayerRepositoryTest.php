<?php

namespace GameBundle\Tests\Repository;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use GameBundle\Entity\Player;
use GameBundle\Repository\PlayerRepository;

class PlayerRepositoryTest extends WebTestCase
{
    protected function setUp()
    {
        self::bootKernel();

        $this->er = static::$kernel->getContainer()->get('doctrine.orm.entity_manager')->getRepository('GameBundle:Player');
    }

    /**
     * Tests the findAllOrderByWins custom repository property, testing if the result is an array, if each element of the array is an instance of Player and if the list is well-ordered
     *
     * @test
     *
     * @return void
    */
    public function testFindAllOrderByWins()
    {
        $players = $this->er->findAllOrderByWins();

        $this->assertTrue(is_array($players));

        foreach($players as $player) {
            $this->assertInstanceOf(Player::class, $player);
        }

        foreach($players as $key => $value) {
            if (isset($players[$key + 1])) {
                $this->assertGreaterThanOrEqual($players[$key + 1]->getWins(), $players[$key]->getWins());
            }
        }
    }
}
