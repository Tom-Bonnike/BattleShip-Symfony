<?php

namespace GameBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use GameBundle\Entity\Player;

/**
 * PlayerRoom
 *
 * @ORM\Table(name="player_room")
 * @ORM\Entity
 */
class PlayerRoom
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="player_id", type="integer", nullable=false)
     */
    private $playerId;

    /**
     * @var array
     *
     * @ORM\Column(name="strikes", type="json_array", nullable=false)
     */
    private $strikes;

    /**
     * @var array
     *
     * @ORM\Column(name="ships", type="json_array", nullable=false)
     */
    private $ships;

    public function __construct()
    {
        $this->strikes = [];
        $this->ships   = [];
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set playerId
     *
     * @param integer $playerId
     *
     * @return PlayerRoom
     */
    public function setPlayerId($playerId)
    {
        $this->playerId = $playerId;

        return $this;
    }

    /**
     * Get playerId
     *
     * @return integer
     */
    public function getPlayerId()
    {
        return $this->playerId;
    }

    /**
     * Set strikes
     *
     * @param array $strikes
     *
     * @return PlayerRoom
     */
    public function setStrikes($strikes)
    {
        $this->strikes = $strikes;

        return $this;
    }

    /**
     * Get strikes
     *
     * @return array
     */
    public function getStrikes()
    {
        return $this->strikes;
    }

    /**
     * Set ships
     *
     * @param array $ships
     *
     * @return PlayerRoom
     */
    public function setShips($ships)
    {
        $this->ships = $ships;

        return $this;
    }

    /**
     * Get ships
     *
     * @return array
     */
    public function getShips()
    {
        return $this->ships;
    }

    /**
     * @ORM\OneToOne(targetEntity="Player")
     * @ORM\JoinColumn(name="player_id", referencedColumnName="id")
     */
    private $player;

    /**
     * Set player
     *
     * @param \GameBundle\Entity\Player $player
     *
     * @return PlayerRoom
     */
    public function setPlayer(Player $player = null)
    {
        $this->player = $player;

        return $this;
    }

    /**
     * Get player
     *
     * @return \GameBundle\Entity\Player
     */
    public function getPlayer()
    {
        return $this->player;
    }
}
