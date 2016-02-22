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
 * @ORM\Entity
 *
 * @ExclusionPolicy("all")
 */
class PlayerRoom
{
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
     * @ORM\Column(name="player_id", type="integer", nullable=false)
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

    public function __construct()
    {
        $this->strikes = [];
        $this->ships   = $this->setShipsRandom();
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
     *
     * @Expose
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

    /**
     * Set ships at random position
     *
     * @return array
     */
    private function setShipsRandom()
    {
        $ships = self::SHIPS;

        // Generate a normal positions array, 0 when nothing, 1 when there's a boat
        $shipsPositionsArray = [];

        for ($i = 0; $i <= 10; $i++){
            for ($j = 0; $j <= 10; $j++){
                $shipsPositionsArray[$i][] = 0;
            }
        }

        $shipsPositions = [];

        foreach ($ships as $ship){
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
                        if ($direction == '+'){
                            $xTemp = $randomPositions[0]++;
                        }

                        else {
                            $xTemp = $randomPositions[0]--;
                        }

                        $yTemp = $randomPositions[1];
                    }

                    else {
                        $xTemp = $randomPositions[0];

                        if ($direction == '+'){
                            $yTemp = $randomPositions[1]++;
                        }

                        else {
                            $yTemp = $randomPositions[1]--;
                        }
                    }

                    $newPositions[] = [$xTemp, $yTemp];
                }

                $positionsAreValid = $this->checkNewPositions($newPositions, $shipsPositionsArray);
            } while ($positionsAreValid === false);

            // Update the normal positions array for the other ships
            foreach ($newPositions as $newPosition){
                $shipsPositionsArray[$newPosition[1]][$newPosition[0]] = 1;
            }

            // Add the positioned ship to a pretty positions array with Battleship-like positions (A1, A2...)
            $shipsPositions[] = [
                'type'      => $ship['type'],
                'positions' => $this->getPrettyPositions($newPositions)
            ];
        }

        // Array with all ship's pretty positions array
        return $shipsPositions;
    }

    /**
     * Get a pretty ship's position array from multiple [x, y] style array
     *
     * @return array
     */
    private function getPrettyPositions($positions)
    {
        $prettyPositions = [];
        $alphabet = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];

        foreach($positions as $position){
            $letterPosition = $alphabet[$position[1]];

            $prettyPositions[] = $letterPosition . ($position[0] + 1);
        }

        return $prettyPositions;
    }

    /**
     * Check if new ship random positions are valid
     *
     * @return boolean
     */
    private function checkNewPositions($newPositions, $positions)
    {
        foreach($newPositions as $newPosition){
            if(($newPosition[0] > 9 || $newPosition[0] < 0)    ||
               ($newPosition[1] > 9 || $newPosition[1] < 0)    ||
               $positions[$newPosition[1]][$newPosition[0]] == 1){
                return false;
            }
        }

        return true;
    }

    public function checkHit($coordinates)
    {
        $ships = $this->getShips();
        $hit = false;

        foreach ($ships as $ship){
            if (in_array($coordinates, $ship['positions'])){
                $hit = true;
                break;
            }
        }

        return $hit;
    }

    public function checkStriked($coordinates)
    {
        $strikes = $this->getStrikes();
        $striked = false;

        foreach ($strikes as $strike){
            if ($strike['coordinates'] == $coordinates){
                $striked = true;
                break;
            }
        }

        return $striked;
    }

    public function updateStrikes($coordinates, $hit)
    {
        $strikes = $this->getStrikes();

        $strikes[] = [
            'hit'         => $hit,
            'coordinates' => $coordinates
        ];

        $this->setStrikes($strikes);
    }

    public function checkWin()
    {
        $hitsNeeded = 0;
        $ships      = self::SHIPS;

        foreach ($ships as $ship){
            $hitsNeeded = $hitsNeeded + $ship['size'];
        }

        $hits = array_filter($this->getStrikes(), function($strike){
            return $strike['hit'];
        });

        $hitsCount = count($hits);

        return $hitsNeeded == $hitsCount;
    }

    public function checkBoatDestroyed($enemyStrikes, $coordinates)
    {
        $boat = array_values(array_filter($this->getShips(), function($ship) use ($coordinates){
            return in_array($coordinates, $ship['positions']);
        }))[0];

        $strikes = array_map(function($strike){
            return $strike['coordinates'];
        }, $enemyStrikes);

        $destroyed = true;

        foreach ($boat['positions'] as $position){
            if (!in_array($position, $strikes)){
                $destroyed = false;
            }
        }

        if ($destroyed){
            return $boat['type'];
        }

        else {
            return false;
        }
    }
}
