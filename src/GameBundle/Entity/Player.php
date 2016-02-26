<?php

namespace GameBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;

use GameBundle\Repository\PlayerRepository;

/**
 * Player
 *
 * @ORM\Table(name="player")
 * @ORM\Entity(repositoryClass="GameBundle\Repository\PlayerRepository")
 *
 * @ExclusionPolicy("all")
*/
class Player
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @Expose
    */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     *
     * @Expose
    */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="token", type="string", length=255, nullable=false)
     *
    */
    private $token;

    /**
     * @var integer
     *
     * @ORM\Column(name="wins", type="integer", nullable=false)
     *
     * @Expose
    */
    private $wins;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creation_date", type="datetime", nullable=false)
     *
     * @Expose
    */
    private $creationDate;

    /**
     * The Player entity constructor, in which is set wins to 0 by default and in which is initialized the creation date attribute to the moment the function is called.
     *
     * @return void
    */
    public function __construct()
    {
        $this->wins = 0;
        $this->creationDate = new \DateTime();
    }


    /**
     * Getter id
     *
     * @return integer
    */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Setter name
     *
     * @param string $name
     *
     * @return Player
    */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Getter name
     *
     * @return string
    */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Setter token
     *
     * @param string $token
     *
     * @return Player
    */
    public function setToken(string $token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Getter token
     *
     * @return string
    */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Setter wins
     *
     * @param int $wins
     *
     * @return Player
    */
    public function setWins(int $wins)
    {
        $this->wins = $wins;

        return $this;
    }

    /**
     * Getter Wins
     *
     * @return int
    */
    public function getWins()
    {
        return $this->wins;
    }

    /**
     * Setter creationDate
     *
     * @param \DateTime $creationDate
     *
     * @return Player
    */
    public function setCreationDate(\DateTime $creationDate)
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    /**
     * Getter creationDate
     *
     * @return \DateTime
    */
    public function getCreationDate()
    {
        return $this->creationDate;
    }
}