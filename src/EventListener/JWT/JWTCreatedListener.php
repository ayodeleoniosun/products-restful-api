<?php

namespace App\EventListener\JWT;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\HttpFoundation\RequestStack;

class JWTCreatedListener
{
    /**
     * @var RequestStack
     */
    private RequestStack $requestStack;

    /**
     * @param  RequestStack  $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @param  JWTCreatedEvent  $event
     *
     * @return void
     */
    public function onJWTCreated(JWTCreatedEvent $event): void
    {
        $request = $this->requestStack->getCurrentRequest();

        $payload = $event->getData();

        if (!empty($event->getUser()->id)) {
            $payload['user_id'] = $event->getUser()->id;
        }

        if (!empty($event->getUser()->email)) {
            $payload['username'] = $event->getUser()->email;
        }

        $event->setData($payload);
    }
}
