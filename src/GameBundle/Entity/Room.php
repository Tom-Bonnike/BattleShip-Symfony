<?php

namespace GameBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;

use GameBundle\Entity\Player;
use GameBundle\Entity\PlayerRoom;
use GameBundle\Repository\RoomRepository;

/**
 * Room
 *
 * @ORM\Table(name="room")
 * @ORM\Entity(repositoryClass="GameBundle\Repository\RoomRepository")
 *
 * @ExclusionPolicy("all")
*/
class Room
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
     * @var integer
     *
     * @ORM\Column(name="player_room_1_id", type="integer", nullable=false)
    */
    private $playerRoom1Id;

    /**
     * @var integer
     *
     * @ORM\Column(name="player_room_2_id", type="integer", nullable=true)
    */
    private $playerRoom2Id;

    /**
     * @var string
     *
     * @ORM\Column(name="winner", type="string", length=255, nullable=false)
     *
     * @Expose
    */
    private $winner;

    /**
     * @var string
     *
     * @ORM\Column(name="turn", type="string", length=255, nullable=false)
    */
    private $turn;

    /**
     * @var boolean
     *
     * @ORM\Column(name="started", type="boolean", nullable=false)
     *
     * @Expose
    */
    private $started;

    /**
     * @var boolean
     *
     * @ORM\Column(name="done", type="boolean", nullable=false)
     *
     * @Expose
    */
    private $done;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creation_date", type="datetime", nullable=false)
     *
     * @Expose
    */
    private $creationDate;

    /**
     * The Room entity constructor, in which is set the winner to a default string 'No winner yet', the started and done statuses to false and in which is initialized the creation date to the moment the function is called
     *
     * @return void
    */
    public function __construct()
    {
        $this->winner       = 'No winner yet!';
        $this->started      = false;
        $this->done         = false;
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
     * Setter playerRoom1Id
     *
     * @param integer $playerRoom1Id
     *
     * @return Room
    */
    public function setPlayerRoom1Id(int $playerRoom1Id)
    {
        $this->playerRoom1Id = $playerRoom1Id;

        return $this;
    }

    /**
     * Getter playerRoom1Id
     *
     * @return integer
    */
    public function getPlayerRoom1Id()
    {
        return $this->playerRoom1Id;
    }

    /**
     * Setter playerRoom2Id
     *
     * @param integer $playerRoom2Id
     *
     * @return Room
    */
    public function setPlayerRoom2Id(int $playerRoom2Id)
    {
        $this->playerRoom2Id = $playerRoom2Id;

        return $this;
    }

    /**
     * Getter playerRoom2Id
     *
     * @return integer
    */
    public function getPlayerRoom2Id()
    {
        return $this->playerRoom2Id;
    }

    /**
     * Setter winner
     *
     * @param string $winner The name of the winner of the game
     *
     * @return Room
    */
    public function setWinner(string $winner)
    {
        $this->winner = $winner;

        return $this;
    }

    /**
     * Getter winner
     *
     * @return string
    */
    public function getWinner()
    {
        return $this->winner;
    }

    /**
     * Setter turn
     *
     * @param string $turn A user token, that will be used to check if it's his turn to play
     *
     * @return Room
    */
    public function setTurn(string $turn)
    {
        $this->turn = $turn;

        return $this;
    }

    /**
     * Getter turn
     *
     * @return string
    */
    public function getTurn()
    {
        return $this->turn;
    }

    /**
     * Setter started
     *
     * @param boolean $started
     *
     * @return Room
    */
    public function setStarted(bool $started)
    {
        $this->started = $started;

        return $this;
    }

    /**
     * Getter started
     *
     * @return boolean
    */
    public function getStarted()
    {
        return $this->started;
    }

    /**
     * Setter done
     *
     * @param boolean $done
     *
     * @return Room
    */
    public function setDone(bool $done)
    {
        $this->done = $done;

        return $this;
    }

    /**
     * Getter done
     *
     * @return boolean
    */
    public function getDone()
    {
        return $this->done;
    }

    /**
     * Setter creationDate
     *
     * @param \DateTime $creationDate
     *
     * @return Room
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

    /**
     * @var PlayerRoom The PlayerRoom object for the player 1
     *
     * @ORM\OneToOne(targetEntity="PlayerRoom", cascade={"persist"})
     * @ORM\JoinColumn(name="player_room_1_id", referencedColumnName="id")
     *
     * @Expose
    */
    private $player1;

    /**
     * @var PlayerRoom The PlayerRoom object for the player 2
     *
     * @ORM\OneToOne(targetEntity="PlayerRoom", cascade={"persist"})
     * @ORM\JoinColumn(name="player_room_2_id", referencedColumnName="id")
     *
     * @Expose
    */
    private $player2;

    /**
     * Setter player1
     *
     * @param \GameBundle\Entity\PlayerRoom $player1
     *
     * @return Room
    */
    public function setPlayer1(PlayerRoom $player1 = null)
    {
        $this->player1 = $player1;

        return $this;
    }

    /**
     * Getter player1
     *
     * @return \GameBundle\Entity\PlayerRoom
    */
    public function getPlayer1()
    {
        return $this->player1;
    }

    /**
     * Setter player2
     *
     * @param \GameBundle\Entity\PlayerRoom $player2
     *
     * @return Room
    */
    public function setPlayer2(PlayerRoom $player2 = null)
    {
        $this->player2 = $player2;

        return $this;
    }

    /**
     * Getter player2
     *
     * @return \GameBundle\Entity\PlayerRoom
    */
    public function getPlayer2()
    {
        return $this->player2;
    }

    /**
     * Returns if the room is full or not
     *
     * @return boolean
    */
    public function isFull()
    {
        if ($this->getPlayer1() && $this->getPlayer2()) {
            return true;
        }

        else {
            return false;
        }
    }

    /**
     * Returns if the room has the specified player or not
     *
     * @param Player $player The player on which the test is made
     *
     * @return boolean
    */
    public function hasPlayer(Player $player)
    {
        $player1 = $this->getPlayer1();
        $player2 = $this->getPlayer2();

        if ($player1->getPlayer() === $player) {
            return true;
        } else if ($player2) {
            if ($player2->getPlayer() === $player) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * The strike method
     *
     * @param string $userToken The userToken specified in the request
     * @param string $coordinates The coordinates that the player wants striked
     *
     * @return array
    */
    public function strike(string $userToken, string $coordinates)
    {
        $player1 = $this->getPlayer1();
        $player2 = $this->getPlayer2();

        // If the player's token matches the player1's userToken
        if ($player1->getPlayer()->getToken() == $userToken) {
            $player      = $player1;
            $enemyPlayer = $player2;
        } else {
            $player      = $player2;
            $enemyPlayer = $player1;
        }

        $striked = $player->checkStriked($coordinates);

        // If it's not been striked already
        if (!$striked) {
            // Check if it's a hit
            $hit = $enemyPlayer->checkHit($coordinates);

            // Add the strike to the player's strike property
            $player->updateStrikes($coordinates, $hit);

            // If it's not a hit
            if (!$hit) {
                // Change turn
                $this->setTurn($enemyPlayer->getPlayer()->getToken());

                $message = 'You missed! It\'s their turn now.';
            } else {
                // Check if the player just won the game
                $win = $player->checkWin();

                // If he did
                if ($win) {
                    // Set the winner name to the player's name and the done status to true, and increment it's wins
                    $this->setWinner($player->getPlayer()->getName());
                    $this->setDone(true);
                    $player->getPlayer()->setWins($player->getPlayer()->getWins() + 1);

                    $message = 'It\'s a hit! Congratulations, you\'ve destroyed their whole fleet! You win!';
                } else {
                    // Check if he destroyed a full boat
                    $boatDestroyed = $enemyPlayer->checkBoatDestroyed($player->getStrikes(), $coordinates);

                    if ($boatDestroyed) {
                        $message = 'It\'s a hit! You destroyed their '. $boatDestroyed .'! You can play again.';
                    } else {
                        $message = 'It\'s a hit! You can play again.';
                    }
                }
            }
        } else {
            $message = 'You have already striked these coordinates! Try again.';
        }

        return [
            'message'     => $message,
            'coordinates' => $coordinates,
            'strikes'     => $player->getStrikes()
        ];
    }

    /**
     * Returns a player's own ships positions
     *
     * @param string $userToken The user token that will be used to check if we return the player's ships to himself and not anyone else
     *
     * @return array
    */
    public function getOwnShips(string $userToken)
    {
        $player1 = $this->getPlayer1();
        $player2 = $this->getPlayer2();

        if ($player1->getPlayer()->getToken() == $userToken) {
            return $player1->getShips();
        } else if ($player2){
            if ($player2->getPlayer()->getToken() == $userToken) {
                return $player2->getShips();
            }
        }

        throw new HttpException(403, 'Sorry, but you can\'t see other players\' ships positions!');
    }
}