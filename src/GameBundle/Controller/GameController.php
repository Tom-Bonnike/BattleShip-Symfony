<?php

namespace GameBundle\Controller;

use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Controller\Annotations\RequestParam;

use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Request\ParamFetcher;

use Symfony\Component\HttpKernel\Exception\HttpException;

use GameBundle\Entity\Room;
use GameBundle\Repository\RoomRepository;

use GameBundle\Entity\Player;
use GameBundle\Repository\PlayerRepository;

use GameBundle\Entity\PlayerRoom;

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
     * @Get("/rooms/active")
    **/
    public function listActiveRoomsAction()
    {
        $er = $this->getDoctrine()->getRepository('GameBundle:Room');

        $rooms = $er->getAllActive();

        $view = $this->view($rooms, 200);
        return $this->handleView($view);
    }

    /**
     * @Post("/room")
    **/
    public function createRoomAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $userToken = $request->headers->get('X-USER-TOKEN');

        $player = $this->validateUserToken($userToken);

        $playerRoom = new PlayerRoom();
        $playerRoom->setPlayer($player);

        $room = new Room();
        $room
            ->setPlayer1($playerRoom)
            ->setTurn($player->getToken());

        $em->persist($room);
        $em->flush();

        $view = $this->view($room, 200);
        return $this->handleView($view);
    }

    /**
     * @Get("/room/{id}")
    **/
    public function getRoomAction(Room $room)
    {
        $view = $this->view($room, 200);
        return $this->handleView($view);
    }

    /**
     * @Put("/room/{id}/join")
    **/
    public function joinRoomAction(Request $request, Room $room)
    {
        $em = $this->getDoctrine()->getManager();
        $userToken = $request->headers->get('X-USER-TOKEN');

        $player = $this->validateUserToken($userToken);

        if (!$room->isFull()){

            if (!$room->hasPlayer($player)){
                $playerRoom = new PlayerRoom();
                $playerRoom->setPlayer($player);

                $room
                    ->setPlayer2($playerRoom)
                    ->setStarted(true);

                $em->persist($room);
                $em->flush();

                $view = $this->view($room, 200);
            }


            else {
                throw new HttpException(401, 'Sorry, but you\'ve already joined this room!');
            }

        }

        else {
            throw new HttpException(401, 'Sorry, but the room is full!');
        }

        return $this->handleView($view);
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
     * @RequestParam(name="name", requirements=".+", allowBlank=false, description="Player name")
     * @Post("/player")
    **/
    public function createPlayerAction(ParamFetcher $paramFetcher)
    {
        $player = new Player();
        $randomToken = bin2hex(openssl_random_pseudo_bytes(10));

        $player
            ->setName($paramFetcher->get('name'))
            ->setToken($randomToken);

        $em = $this->getDoctrine()->getManager();
        $em->persist($player);
        $em->flush();

        $view = $this->view($player, 200);
        return $this->handleView($view);
    }

    /**
     * @Get("/player/{id}")
    **/
    public function showPlayerAction(Player $player)
    {
        $view = $this->view($player, 200);
        return $this->handleView($view);
    }

    private function validateUserToken($userToken)
    {
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