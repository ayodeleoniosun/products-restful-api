<?php

namespace App\EventListener\JWT;

use App\Exception\CustomException;
use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTDecodedEvent;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class JWTDecodedListener
{
    /**
     * @param  RequestStack  $requestStack
     * @param  UserRepository  $userRepository
     */
    public function __construct(public RequestStack $requestStack, public UserRepository $userRepository)
    {

    }

    /**
     * @param  JWTDecodedEvent  $event
     *
     * @return void
     */
    public function onJWTDecoded(JWTDecodedEvent $event): void
    {
        $payload = $event->getPayload();

        $user = $this->userRepository->find($payload['user_id']);

        if (!$user) {
            throw  new CustomException("Invalid token supplied", Response::HTTP_UNAUTHORIZED);
        }
    }
}