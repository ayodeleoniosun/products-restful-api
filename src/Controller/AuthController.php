<?php

declare(strict_types = 1);

namespace App\Controller;

use App\Enum\StatusEnum;
use App\Exception\CustomException;
use App\Service\AuthService;
use DateMalformedStringException;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AuthController extends AbstractController
{
    public function __construct(
        public AuthService $authService,
        public SerializerInterface $serializer,
        public UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function login(Request $request, JWTTokenManagerInterface $JWTManager): Response
    {
        $response = $this->authService->login($request, $this->serializer, $this->passwordHasher, $JWTManager);

        return new JsonResponse([
            'status' => StatusEnum::SUCCESS->value,
            'message' => 'Login successful',
            'token' => $response,
        ], Response::HTTP_OK);
    }

    /**
     * @throws DateMalformedStringException
     * @throws CustomException
     */
    public function register(
        Request $request,
        ValidatorInterface $validator,
        EntityManagerInterface $entityManager,
    ): Response {
        $response = $this->authService->register($request, $validator, $this->serializer, $this->passwordHasher,
            $entityManager);

        return new JsonResponse([
            'status' => StatusEnum::SUCCESS->value,
            'message' => 'User successfully registered',
            'data' => json_decode($response),
        ], Response::HTTP_CREATED);
    }
}