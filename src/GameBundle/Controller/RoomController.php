<?php

namespace GameBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Request\ParamFetcher;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Validator\Constraints as Assert;

use GameBundle\Entity\Room;
use GameBundle\Repository\RoomRepository;

use GameBundle\Entity\PlayerRoom;

/**
 * Class for Room related API actions
*/
class RoomController extends FOSRestController
{
    /**
     * Gets all rooms
     *
     * @return View
     *
     * @Get("/rooms")
    */
    public function listRoomsAction()
    {
        $er = $this->getDoctrine()->getRepository('GameBundle:Room');

        $rooms = $er->findAll();

        $view = $this->view($rooms, 200);
        return $this->handleView($view);
    }

    /**
     * Gets all rooms that haven't started yet
     *
     * @return View
     *
     * @Get("/rooms/waiting")
    */
    public function listWaitingRoomsAction()
    {
        $er = $this->getDoctrine()->getRepository('GameBundle:Room');

        $rooms = $er->findByStarted(false);

        $view = $this->view($rooms, 200);
        return $this->handleView($view);
    }

    /**
     * Gets all rooms in which games are NOT done
     *
     * @return View
     *
     * @Get("/rooms/active")
    */
    public function listActiveRoomsAction()
    {
        $er = $this->getDoctrine()->getRepository('GameBundle:Room');

        $rooms = $er->findByDone(false);

        $view = $this->view($rooms, 200);
        return $this->handleView($view);
    }

    /**
     * Gets all rooms in which games are finished
     *
     * @return View
     *
     * @Get("/rooms/finished")
    */
    public function listFinishedRoomsAction()
    {
        $er = $this->getDoctrine()->getRepository('GameBundle:Room');

        $rooms = $er->findByDone(true);

        $view = $this->view($rooms, 200);
        return $this->handleView($view);
    }

    /**
     * Creates a room
     *
     * @param Request $request The request object, in which must be set a "X-USER-TOKEN" header's value with the user's token set for authentification
     *
     * @return View
     *
     * @Post("/rooms")
    */
    public function createRoomAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $userToken = $request->headers->get('X-USER-TOKEN');
        $player    = $this->get('usertoken')->validate($userToken);

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

        $url = $this->generateUrl('show_room', array('id' => $room->getId()));
        $view->setLocation($url);

        return $this->handleView($view);
    }

    /**
     * Gets one room by id
     *
     * @param Room $room The room object fetched by ID
     *
     * @return View
     *
     * @Get("/room/{id}")
    */
    public function showRoomAction(Room $room = null)
    {
        if ($room) {
            $view = $this->view($room, 200);
            return $this->handleView($view);
        } else {
            throw new HttpException(404, 'Sorry, but this room doesn\'t exist.');
        }
    }

    /**
     * Join a room
     *
     * @param Request $request The request object, in which must be set a "X-USER-TOKEN" header's value with the user's token set for authentification
     * @param Room $room The room object fetched by ID
     *
     * @return View
     *
     * @Put("/room/{id}/join")
    */
    public function joinRoomAction(Request $request, Room $room = null)
    {
        if ($room) {
            $em = $this->getDoctrine()->getManager();

            $userToken = $request->headers->get('X-USER-TOKEN');
            $player    = $this->get('usertoken')->validate($userToken);

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
                    } else {
                        throw new HttpException(403, 'Sorry, but you\'ve already joined this room!');
                    }
                } else {
                    throw new HttpException(403, 'Sorry, but the room is full!');
                }
            } else {
                throw new HttpException(403, 'Sorry, but the game has already started!');
            }

            return $this->handleView($view);
        } else {
            throw new HttpException(404, 'Sorry, but this room doesn\'t exist.');
        }
    }

    /**
     * Gets your own ships if the given user-token allows it
     *
     * @param Request $request The request object, in which must be set a "X-USER-TOKEN" header's value with the user's token set for authentification
     * @param Room $room The room object fetched by ID
     *
     * @return View
     *
     * @Get("/room/{id}/ships")
    */
    public function getOwnShipsAction(Request $request, Room $room = null)
    {
        if ($room) {
            $userToken = $request->headers->get('X-USER-TOKEN');
            $player    = $this->get('usertoken')->validate($userToken);

            $ships = $room->getOwnShips($userToken);

            $view = $this->view($ships, 200);
            return $this->handleView($view);
        } else {
            throw new HttpException(404, 'Sorry, but this room doesn\'t exist.');
        }
    }

    /**
     * Strikes the enemy if it's your turn
     *
     * @param ParamFetcher $paramFetcher The FOSREST Param fetcher, that will be used to fetch the coordinates for the strike
     * @param Request $request The request object, in which must be set a "X-USER-TOKEN" header's value with the user's token set for authentification along with a "coordinates" data in the request body, in the format "A1" to "J10"
     * @param Room $room The room object fetched by ID
     *
     * @return View
     *
     * @RequestParam(name="coordinates", requirements=@Assert\Regex("/^([a-jA-J]([1-9]|10))$/"), allowBlank=false, description="Strike coordinates", nullable=false)
     * @Put("/room/{id}/strike")
    */
    public function roomStrikeAction(ParamFetcher $paramFetcher, Request $request, Room $room = null)
    {
        if ($room) {
            $userToken = $request->headers->get('X-USER-TOKEN');
            $player    = $this->get('usertoken')->validate($userToken);

            if (!$room->getDone()) {
                if ($room->getStarted()) {
                    if ($room->getTurn() == $userToken) {
                        $em = $this->getDoctrine()->getManager();

                        // Always check uppercased coordinates
                        $coordinates = (string) strtoupper($paramFetcher->get('coordinates'));

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
                    } else {
                        throw new HttpException(403, 'Sorry, but it\'s not your turn!');
                    }
                } else {
                    throw new HttpException(403, 'Sorry, but the game hasn\'t started yet!');
                }
            } else {
                throw new HttpException(403, 'Sorry, but the game has already ended.');
            }
        } else {
            throw new HttpException(404, 'Sorry, but this room doesn\'t exist.');
        }
    }
}