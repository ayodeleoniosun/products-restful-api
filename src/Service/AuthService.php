<?php

namespace App\Service;

use App\Entity\User;
use App\Enum\StatusEnum;
use App\Repository\UserRepository;
use DateMalformedStringException;
use Doctrine\ORM\EntityManagerInterface;
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
     */
    public function register(
        Request $request,
        ValidatorInterface $validator,
        SerializerInterface $serializer,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
    ): array {
        $data = $request->getContent();
        $user = $serializer->deserialize($data, User::class, 'json');

        $validationResponse = $this->validateRegistrationPayload($validator, $user);

        if (isset($validationResponse['status']) && $validationResponse['status'] === StatusEnum::ERROR->value) {
            return [
                'status' => $validationResponse['status'],
                'message' => $validationResponse['message'],
            ];
        }

        $userExist = $this->userRepository->findOneBy(['email' => $user->email]);

        if ($userExist) {
            return [
                'status' => StatusEnum::ERROR->value,
                'message' => 'User already exist',
            ];
        }

        $user->password = $passwordHasher->hashPassword($user, $user->password);
        $user->createdAt = now();
        $user->updatedAt = now();

        $this->userRepository->create($entityManager, $user);

        $data = $serializer->serialize($user, 'json');

        return [
            'status' => StatusEnum::SUCCESS->value,
            'message' => 'User successfully registered',
            'data' => $data,
        ];
    }

    protected function validateRegistrationPayload(ValidatorInterface $validator, User $user): ?array
    {
        $errors = $validator->validate($user);

        if (count($errors) === 0) {
            return null;
        }

        return [
            'status' => StatusEnum::ERROR->value,
            'message' => $errors[0]->getMessage(),
        ];
    }
}