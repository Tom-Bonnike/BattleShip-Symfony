<?php

namespace GameBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Request\ParamFetcher;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Validator\Constraints as Assert;

use GameBundle\Entity\Player;
use GameBundle\Repository\PlayerRepository;

/**
 * Class for Player related API actions
*/
class PlayerController extends FOSRestController
{
    /**
     * Gets all players
     *
     * @return View
     *
     * @Get("/players")
    */
    public function listPlayersAction()
    {
        $er      = $this->getDoctrine()->getRepository('GameBundle:Player');
        $players = $er->findAll();

        $view = $this->view($players, 200);
        return $this->handleView($view);
    }

    /**
     * Gets all players ordered by wins in descending order
     *
     * @return View
     *
     * @Get("/players/wins")
    */
    public function listPlayersByWinsAction()
    {
        $er      = $this->getDoctrine()->getRepository('GameBundle:Player');
        $players = $er->findAllOrderByWins();

        $view = $this->view($players, 200);
        return $this->handleView($view);
    }

    /**
     * Creates a player
     *
     * @param ParamFetcher $paramFetcher The FOSREST Param fetcher, that will be used to fetch the player's name
     *
     * @return View
     *
     * @RequestParam(name="name", requirements=@Assert\Regex("/^[^\s]{1,20}$/"), allowBlank=false, description="Player name", nullable=false)
     * @Post("/players")
    */
    public function createPlayerAction(ParamFetcher $paramFetcher)
    {
        $em   = $this->getDoctrine()->getManager();
        $name = $paramFetcher->get('name');

        if ($em->getRepository('GameBundle:Player')->findOneByName($name)) {
            throw new HttpException(400, 'Sorry, but this name is already taken. Pick another one.');
        } else {
            // Create a new Player Entity
            $player = new Player();

            // Generate random token that will be passed in requests' headers to recognize a specific user
            $randomToken = $this->get('usertoken')->generate();

            // Set name and Token
            $player
                ->setName($name)
                ->setToken($randomToken);

            // Persist the player
            $em->persist($player);
            $em->flush();

            $response = [
                'player'  => $player,
                'token'   => $player->getToken(), // Send back the token, that won't be displayed again.
                'message' => 'Keep this token secret! You won\'t be able to retrieve it and you need it for some API calls.'
            ];

            $view = $this->view($response, 201);

            $url = $this->generateUrl('show_player', array('id' => $player->getId()));
            $view->setLocation($url);

            return $this->handleView($view);
        }
    }

    /**
     * Gets one player by id or username
     *
     * @param mixed $id The id route parameter. If it doesn't match a player, it will be used to search for the player by name
     * @param Player $player The player object fetched by ID if $id matches one
     *
     * @return View
     *
     * @Get("/player/{id}")
    */
    public function showPlayerAction($id, Player $player = null)
    {
        if ($player) {
            $view = $this->view($player, 200);
            return $this->handleView($view);
        } else {
            $er     = $this->getDoctrine()->getRepository('GameBundle:Player');
            $player = $er->findOneByName($id);

            if ($player) {
                $view = $this->view($player, 200);
                return $this->handleView($view);
            } else {
                throw new HttpException(404, 'Sorry, but this user doesn\'t exist.');
            }
        }
    }

    /**
     * Deletes a player
     *
     * @param Request $request The request object, in which must be set a "X-USER-TOKEN" header's value with the user's token set for authentification so that a player can only delete its own account
     * @param mixed $id The id route parameter. If it doesn't match a player, it will be used to search for the player by name
     * @param Player $player The player object fetched by ID if $id matches one
     *
     * @return View
     *
     * @Delete("/player/{id}")
    */
    public function deletePlayerAction(Request $request, $id, Player $player = null)
    {
        $userToken     = $request->headers->get('X-USER-TOKEN');
        $playerByToken = $this->get('usertoken')->validate($userToken);

        if ($player) {
            if ($player == $playerByToken) {
                $em = $this->getDoctrine()->getManager();
                $em->remove($player);
                $em->flush();

                $view = $this->view([], 204);
                return $this->handleView($view);
            } else {
                throw new HttpException(403, 'Sorry, but you can\'t delete another player\'s account.');
            }
        } else {
            $er     = $this->getDoctrine()->getRepository('GameBundle:Player');
            $player = $er->findOneByName($id);

            if ($player) {
                $em = $this->getDoctrine()->getManager();
                $em->remove($player);
                $em->flush();

                $view = $this->view([], 204);
                return $this->handleView($view);
            } else {
                throw new HttpException(404, 'Sorry, but this user doesn\'t exist.');
            }
        }
    }
}