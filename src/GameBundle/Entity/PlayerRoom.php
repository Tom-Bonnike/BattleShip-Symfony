<?php

namespace GameBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;

use GameBundle\Entity\Player;

/**
 * PlayerRoom
 *
 * @ORM\Table(name="player_room")
 * @ORM\Entity(repositoryClass="GameBundle\Repository\PlayerRoomRepository")
 *
 * @ExclusionPolicy("all")
*/
class PlayerRoom
{
    /**
     * @var array Constant in which the different type of boats and their sizes are stored.
    */
    const SHIPS = [
        [
            'type' => 'Aircraft Carrier',
            'size' => 5
        ],
        [
            'type' => 'Battleship',
            'size' => 4
        ],
        [
            'type' => 'Submarine',
            'size' => 3
        ],
        [
            'type' => 'Cruiser',
            'size' => 3
        ],
        [
            'type' => 'Patrol boat',
            'size' => 2
        ]
    ];

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
     * @ORM\Column(name="player_id", type="integer", nullable=true)
    */
    private $playerId;

    /**
     * @var array
     *
     * @ORM\Column(name="strikes", type="json_array", nullable=false)
     *
     * @Expose
    */
    private $strikes;

    /**
     * @var array
     *
     * @ORM\Column(name="ships", type="json_array", nullable=false)
     *
    */
    private $ships;

    /**
     * The PlayerRoom entity constructor, in which is set the strikes to an empty array by default and in which are set the ships positions to random.
     *
     * @return void
    */
    public function __construct()
    {
        $this->strikes = [];
        $this->ships   = $this->setShipsRandom();
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
     * Setter playerId
     *
     * @param integer $playerId
     *
     * @return PlayerRoom
    */
    public function setPlayerId(int $playerId)
    {
        $this->playerId = $playerId;

        return $this;
    }

    /**
     * Getter playerId
     *
     * @return integer
    */
    public function getPlayerId()
    {
        return $this->playerId;
    }

    /**
     * Setter strikes
     *
     * @param array $strikes
     *
     * @return PlayerRoom
    */
    public function setStrikes(array $strikes)
    {
        $this->strikes = $strikes;

        return $this;
    }

    /**
     * Getter strikes
     *
     * @return array
    */
    public function getStrikes()
    {
        return $this->strikes;
    }

    /**
     * Setter ships
     *
     * @param array $ships
     *
     * @return PlayerRoom
    */
    public function setShips(array $ships)
    {
        $this->ships = $ships;

        return $this;
    }

    /**
     * Getter ships
     *
     * @return array
    */
    public function getShips()
    {
        return $this->ships;
    }

    /**
     * @var Player The player property, joined on the player_id column by id key
     *
     * @ORM\OneToOne(targetEntity="Player")
     * @ORM\JoinColumn(name="player_id", referencedColumnName="id", nullable=true)
     *
     * @Expose
    */
    private $player;

    /**
     * Setter player
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
     * Getter player
     *
     * @return \GameBundle\Entity\Player
    */
    public function getPlayer()
    {
        return $this->player;
    }

    /**
     * Sets ships at random position
     *
     * @return array
    */
    private function setShipsRandom()
    {
        $ships = self::SHIPS;

        // Generate a normal positions array, 0 when nothing, 1 when there's a boat
        $shipsPositionsArray = [];

        for ($i = 0; $i <= 10; $i++) {
            for ($j = 0; $j <= 10; $j++) {
                $shipsPositionsArray[$i][] = 0;
            }
        }

        $shipsPositions = [];

        foreach ($ships as $ship) {
            // For each ship, set it at a random position
            do {
                // Choose a random position
                $randomPositions = [mt_rand(0,9), mt_rand(0,9)];

                // Randomly select vertical or horizontal axis
                $axis = ['x', 'y'][mt_rand(0, 1)];

                // Randomly select positive or negative on the selected axis
                $direction = ['+', '-'][mt_rand(0, 1)];

                $newPositions = [];

                for ($i = 0; $i < $ship['size']; $i++){
                    if ($axis == 'x'){
                        if ($direction == '+') {
                            $xTemp = $randomPositions[0]++;
                        } else {
                            $xTemp = $randomPositions[0]--;
                        }

                        $yTemp = $randomPositions[1];
                    }

                    else {
                        if ($direction == '+') {
                            $yTemp = $randomPositions[1]++;
                        } else {
                            $yTemp = $randomPositions[1]--;
                        }

                        $xTemp = $randomPositions[0];
                    }

                    $newPositions[] = [$xTemp, $yTemp];
                }

                $positionsAreValid = $this->checkNewPositions($newPositions, $shipsPositionsArray);
            } while ($positionsAreValid === false);

            // Update the normal positions array for the other ships
            foreach ($newPositions as $newPosition) {
                $shipsPositionsArray[$newPosition[1]][$newPosition[0]] = 1;
            }

            // Add the positioned ship to a pretty positions array with Battleship-like positions (A1, A2...)
            $shipsPositions[] = [
                'type'      => $ship['type'],
                'positions' => $this->getPrettyPositions($newPositions)
            ];
        }

        // Array with all ship's pretty positions
        return $shipsPositions;
    }

    /**
     * Gets a pretty ship's position array from multiple [x, y] style array
     *
     * @param array $positions The positions that we want prettied
     *
     * @return array
    */
    private function getPrettyPositions(array $positions)
    {
        $prettyPositions = [];
        $alphabet = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];

        foreach($positions as $position) {
            $letterPosition = $alphabet[$position[1]];

            $prettyPositions[] = $letterPosition . ($position[0] + 1);
        }

        return $prettyPositions;
    }

    /**
     * Checks if new ship random positions are valid
     *
     * @param array $newPositions The old positions that are used for the check
     * @param array $positions The new positions that are used for the check
     *
     * @return boolean
    */
    private function checkNewPositions(array $newPositions, array $positions)
    {
        foreach($newPositions as $newPosition) {
            if(($newPosition[0] > 9 || $newPosition[0] < 0)    ||
               ($newPosition[1] > 9 || $newPosition[1] < 0)    ||
               $positions[$newPosition[1]][$newPosition[0]] == 1) {
                return false;
            }
        }

        return true;
    }

    /**
     * Checks if the coordinates that are being striked have already been striked
     *
     * @param string $coordinates The coordinates that are being checked
     *
     * @return boolean
    */
    public function checkStriked(string $coordinates)
    {
        $strikes = $this->getStrikes();
        $striked = false;

        foreach ($strikes as $strike) {
            if ($strike['coordinates'] == $coordinates) {
                $striked = true;
                break;
            }
        }

        return $striked;
    }

    /**
     * Checks if the coordinates that are being striked hit a player's ship
     *
     * @param string $coordinates The coordinates that are being checked for a hit
     *
     * @return boolean
    */
    public function checkHit(string $coordinates)
    {
        $ships = $this->getShips();
        $hit = false;

        foreach ($ships as $ship) {
            if (in_array($coordinates, $ship['positions'])) {
                $hit = true;
                break;
            }
        }

        return $hit;
    }

    /**
     * Update the user's strikes, adding the new strikes and their status to the list
     *
     * @param string $coordinates The coordinates that are being checked
     * @param boolean $hit Whether it's a hit or not
     *
     * @return PlayerRoom
    */
    public function updateStrikes(string $coordinates, bool $hit)
    {
        $strikes = $this->getStrikes();

        $strikes[] = [
            'hit'         => $hit,
            'coordinates' => $coordinates
        ];

        $this->setStrikes($strikes);

        return $this;
    }

    /**
     * Checks if the user that just striked an enemy's ship has won or not
     *
     * @return boolean
    */
    public function checkWin()
    {
        $hitsNeeded = 0;
        $ships      = self::SHIPS;

        foreach ($ships as $ship) {
            $hitsNeeded = $hitsNeeded + $ship['size'];
        }

        $hits = array_filter($this->getStrikes(), function($strike) {
            return $strike['hit'];
        });

        $hitsCount = count($hits);

        return $hitsNeeded == $hitsCount;
    }

    /**
     * Checks if the user that just striked an enemy's ship has destroyed a full boat or not
     *
     * @param array $enemyStrikes The array of strikes of the enemy, in which will be checked if all positions of the boat that's been hit have also been hit
     * @param string $coordinates The coordinates that are being checked
     *
     * @return mixed
    */
    public function checkBoatDestroyed(array $enemyStrikes, string $coordinates)
    {
        $boat = array_values(array_filter($this->getShips(), function($ship) use ($coordinates) {
            return in_array($coordinates, $ship['positions']);
        }))[0];

        $strikes = array_map(function($strike) {
            return $strike['coordinates'];
        }, $enemyStrikes);

        $destroyed = true;

        foreach ($boat['positions'] as $position) {
            if (!in_array($position, $strikes)) {
                $destroyed = false;
            }
        }

        if ($destroyed) {
            return $boat['type'];
        } else {
            return false;
        }
    }
}