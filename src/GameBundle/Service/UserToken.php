<?php

namespace GameBundle\Service;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpKernel\Exception\HttpException;
use GameBundle\Repository\PlayerRepository;

/**
 * Service used to manage the user token
*/
class UserToken
{
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Validates whether the user token was sent in the request and if it's linked to an account or not.
     *
     * @param string $userToken The user token on which the tests are made
     *
     * @return Player
    **/
    public function validate(string $userToken)
    {
        if (!$userToken) {
            throw new HttpException(401, 'Please authenticate (X-USER-TOKEN header parameter) with the token that was given to you at your account\'s creation.');
        } else {
            $er = $this->em->getRepository('GameBundle:Player');
            $player = $er->findOneByToken($userToken);

            if ($player) {
                return $player;
            } else {
                throw new HttpException(401, 'Sorry, but this user token isn\'t linked to any player.');
            }
        }
    }

    /**
     * Generates an user token
     *
     * @return String
    **/
    public function generate()
    {
        return bin2hex(openssl_random_pseudo_bytes(10));
    }
}