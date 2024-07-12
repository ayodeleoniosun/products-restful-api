<?php

namespace App\Service;

use App\Entity\User;
use App\Exception\CustomException;
use App\Repository\UserRepository;
use DateMalformedStringException;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use function Symfony\Component\Clock\now;

class AuthService
{
    public function __construct(public UserRepository $userRepository)
    {
    }

    /**
     * @throws DateMalformedStringException
     * @throws CustomException
     */
    public function register(
        Request $request,
        ValidatorInterface $validator,
        SerializerInterface $serializer,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
    ): JsonResponse|string {
        $user = $this->serializePayload($request, $serializer);

        $this->validateRegistrationPayload($validator, $user);

        $userExist = $this->userRepository->findOneBy(['email' => $user->email]);

        if ($userExist) {
            throw new CustomException('User already exist');
        }

        $user->password = $passwordHasher->hashPassword($user, $user->password);
        $user->createdAt = now();
        $user->updatedAt = now();

        $this->userRepository->create($entityManager, $user);

        return $serializer->serialize($user, 'json');
    }

    public function serializePayload(Request $request, SerializerInterface $serializer)
    {
        $data = $request->getContent();
        return $serializer->deserialize($data, User::class, 'json');
    }

    /**
     * @throws CustomException
     */
    protected function validateRegistrationPayload(ValidatorInterface $validator, User $user): ?array
    {
        $errors = $validator->validate($user);

        if (count($errors) === 0) {
            return null;
        }

        throw new CustomException($errors[0]->getMessage());
    }

    public function login(
        Request $request,
        SerializerInterface $serializer,
        UserPasswordHasherInterface $passwordHasher,
        JWTTokenManagerInterface $JWTManager,
    ): JsonResponse|string {
        $user = $this->serializePayload($request, $serializer);

        $getUser = $this->userRepository->findOneBy(['email' => $user->email]);

        if (!$getUser) {
            throw new CustomException('User not found');
        }

        $isPasswordValid = $passwordHasher->isPasswordValid($getUser, $user->password);

        if (!$isPasswordValid) {
            throw new CustomException('Invalid login credentials');
        }

        return $JWTManager->create($getUser);
    }
}