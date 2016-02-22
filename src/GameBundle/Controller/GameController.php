<?php

namespace GameBundle\Controller;

use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Controller\Annotations\RequestParam;

use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Request\ParamFetcher;
use Symfony\Component\Validator\Constraints as Assert;

use Symfony\Component\HttpKernel\Exception\HttpException;

use GameBundle\Entity\Player;
use GameBundle\Repository\PlayerRepository;

use GameBundle\Entity\Room;
use GameBundle\Repository\RoomRepository;

use GameBundle\Entity\PlayerRoom;

/*
    Todo:
        - tests unitaires
        - doc & commentaires & README & postman collection
*/

class GameController extends FOSRestController
{
    /****              ****/
    /**** ROOMS ROUTES ****/
    /****              ****/

    /**
     * @Get("/rooms")
    **/
    public function listRoomsAction()
    {
        $er = $this->getDoctrine()->getRepository('GameBundle:Room');

        $rooms = $er->findAll();

        $view = $this->view($rooms, 200);
        return $this->handleView($view);
    }

    /**
     * @Get("/rooms/waiting")
    **/
    public function listWaitingRoomsAction()
    {
        $er = $this->getDoctrine()->getRepository('GameBundle:Room');

        $rooms = $er->findByStarted(false);

        $view = $this->view($rooms, 200);
        return $this->handleView($view);
    }

    /**
     * @Get("/rooms/active")
    **/
    public function listActiveRoomsAction()
    {
        $er = $this->getDoctrine()->getRepository('GameBundle:Room');

        $rooms = $er->findByDone(false);

        $view = $this->view($rooms, 200);
        return $this->handleView($view);
    }

    /**
     * @Get("/rooms/finished")
    **/
    public function listFinishedRoomsAction()
    {
        $er = $this->getDoctrine()->getRepository('GameBundle:Room');

        $rooms = $er->findByDone(true);

        $view = $this->view($rooms, 200);
        return $this->handleView($view);
    }

    /**
     * @Post("/rooms")
    **/
    public function createRoomAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $userToken = $request->headers->get('X-USER-TOKEN');
        $player = $this->validateUserToken($userToken);

        // Create PlayerRoom entity
        $playerRoom = new PlayerRoom();
        $playerRoom->setPlayer($player);

        // Create Room entity
        $room = new Room();
        $room
            ->setPlayer1($playerRoom) // set the playerRoom entity to the room's player1 property
            ->setTurn($player->getToken());

        // Persist the room entity (playerRoom cascade persist)
        $em->persist($room);
        $em->flush();

        $view = $this->view($room, 201);
        return $this->handleView($view);
    }

    /**
     * @Get("/room/{id}")
    **/
    public function getRoomAction(Room $room = null)
    {
        if ($room){
            $view = $this->view($room, 200);
            return $this->handleView($view);
        }

        else {
            throw new HttpException(404, 'Sorry, but this room doesn\'t exist.');
        }
    }

    /**
     * @Put("/room/{id}/join")
    **/
    public function joinRoomAction(Request $request, Room $room = null)
    {
        if ($room){
            $em = $this->getDoctrine()->getManager();

            $userToken = $request->headers->get('X-USER-TOKEN');
            $player = $this->validateUserToken($userToken);

            if (!$room->getStarted()){
                if (!$room->isFull()){

                    if (!$room->hasPlayer($player)){
                        // Create a new PlayerRoom entity
                        $playerRoom = new PlayerRoom();
                        $playerRoom->setPlayer($player);

                        $room
                            ->setPlayer2($playerRoom) // Set the PlayerRoom entity to the room's player2 property
                            ->setStarted(true); // Start the game

                        $em->persist($room);
                        $em->flush();

                        $view = $this->view($room, 201);
                    }


                    else {
                        throw new HttpException(403, 'Sorry, but you\'ve already joined this room!');
                    }

                }

                else {
                    throw new HttpException(403, 'Sorry, but the room is full!');
                }
            }

            else {
                throw new HttpException(403, 'Sorry, but the game has already started!');
            }

            return $this->handleView($view);
        }

        else {
            throw new HttpException(404, 'Sorry, but this room doesn\'t exist.');
        }
    }

    /**
     * @Get("/room/{id}/ships")
    **/
    public function getOwnShipsAction(Request $request, Room $room = null)
    {
        if ($room){
            $userToken = $request->headers->get('X-USER-TOKEN');
            $player = $this->validateUserToken($userToken);

            $ships = $room->getOwnShips($userToken);

            $view = $this->view($ships, 200);
            return $this->handleView($view);
        }

        else {
            throw new HttpException(404, 'Sorry, but this room doesn\'t exist.');
        }
    }

    /**
     * @RequestParam(name="coordinates", requirements=@Assert\Regex("/^([a-jA-J]([1-9]|10))$/"), allowBlank=false, description="Strike coordinates", nullable=false)
     * @Put("/room/{id}/strike")
    **/
    public function roomStrikeAction(ParamFetcher $paramFetcher, Request $request, Room $room = null)
    {
        if ($room){
            $userToken = $request->headers->get('X-USER-TOKEN');
            $player = $this->validateUserToken($userToken);

            if (!$room->getDone()){
                if ($room->getStarted()){
                    if ($room->getTurn() == $userToken){
                        $em = $this->getDoctrine()->getManager();

                        // Always check uppercased coordinates
                        $coordinates = strtoupper($paramFetcher->get('coordinates'));

                        // Manage the strike and store the response that will be sent back
                        $strikeResponse = $room->strike($userToken, $coordinates);

                        // Persist the room (and strikes, cascaded)
                        $em->persist($room);

                        // Persist the players entity in case of them having won the game
                        $em->persist($room->getPlayer1()->getPlayer());
                        $em->persist($room->getPlayer2()->getPlayer());

                        // Flush
                        $em->flush();

                        $view = $this->view($strikeResponse, 201);
                        return $this->handleView($view);
                    }

                    else {
                        throw new HttpException(403, 'Sorry, but it\'s not your turn!');
                    }
                }

                else {
                    throw new HttpException(403, 'Sorry, but the game hasn\'t started yet!');
                }
            }

            else {
                throw new HttpException(403, 'Sorry, but the game has already ended.');
            }
        }

        else {
            throw new HttpException(404, 'Sorry, but this room doesn\'t exist.');
        }
    }

    /****                ****/
    /**** PLAYERS ROUTES ****/
    /****                ****/

    /**
     * @Get("/players")
    **/
    public function listPlayersAction()
    {
        $er = $this->getDoctrine()->getRepository('GameBundle:Player');

        $players = $er->findAll();

        $view = $this->view($players, 200);
        return $this->handleView($view);
    }

    /**
     * @Get("/players/wins")
    **/
    public function listPlayersByWinsAction()
    {
        $er = $this->getDoctrine()->getRepository('GameBundle:Player');

        $players = $er->findAllOrderByWins();

        $view = $this->view($players, 200);
        return $this->handleView($view);
    }

    /**
     * @RequestParam(name="name", requirements=".+", allowBlank=false, description="Player name", nullable=false)
     * @Post("/players")
    */
    public function createPlayerAction(ParamFetcher $paramFetcher)
    {
        $em = $this->getDoctrine()->getManager();

        // Create a new Player Entity
        $player = new Player();

        // Generate random token that will be passed in requests' headers to recognize a specific user
        $randomToken = bin2hex(openssl_random_pseudo_bytes(10));

        $player
            ->setName($paramFetcher->get('name'))
            ->setToken($randomToken);

        // Persist the player
        $em->persist($player);
        $em->flush();

        $response = [
            'player' => $player,
            'token'  => $player->getToken()
        ];

        $view = $this->view($response, 201);
        return $this->handleView($view);
    }

    /**
     * @Get("/player/{id}")
    */
    public function showPlayerAction($id, Player $player = null)
    {
        if ($player){
            $view = $this->view($player, 200);
            return $this->handleView($view);
        }

        else {
            $er = $this->getDoctrine()->getRepository('GameBundle:Player');
            $player = $er->findOneByName($id);

            if ($player){
                $view = $this->view($player, 200);
                return $this->handleView($view);
            }

            else {
                throw new HttpException(404, 'Sorry, but this user doesn\'t exist.');
            }
        }
    }

    /**
     * Validates the user token
     *
     * @return Player
    **/
    private function validateUserToken($userToken)
    {
        if (!$userToken){
            throw new HttpException(401, 'Please authenticate (X-USER-TOKEN header parameter) with the token that was given to you at your account\'s creation.');
        }

        else {
            $er = $this->getDoctrine()->getRepository('GameBundle:Player');
            $player = $er->findOneByToken($userToken);

            if ($player){
                return $player;
            }

            else {
                throw new HttpException(401, 'Sorry, but this user token isn\'t linked to any player.');
            }
        }
    }
}