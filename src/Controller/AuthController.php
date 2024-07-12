<?php

declare(strict_types = 1);

namespace App\Controller;

use App\Enum\StatusEnum;
use App\Service\AuthService;
use DateMalformedStringException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AuthController extends AbstractController
{
    public function __construct(public AuthService $authService)
    {
    }

    /**
     * @throws DateMalformedStringException
     */
    #[Route('/register', name: 'register')]
    public function register(
        Request $request,
        ValidatorInterface $validator,
        SerializerInterface $serializer,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
    ): Response {
        $response = $this->authService->register($request, $validator, $serializer, $passwordHasher, $entityManager);

        if ($response['status'] === StatusEnum::ERROR->value) {
            return new JsonResponse([
                'status' => StatusEnum::ERROR->value,
                'message' => $response['message'],
            ], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse([
            'status' => StatusEnum::SUCCESS->value,
            'message' => 'User successfully registered',
            'data' => json_decode($response['data']),
        ], Response::HTTP_CREATED);
    }
}