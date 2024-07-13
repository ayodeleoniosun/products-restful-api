<?php

declare(strict_types = 1);

namespace App\Controller;

use App\Enum\StatusEnum;
use App\Service\ProductService;
use DateMalformedStringException;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductController extends AbstractController
{
    public function __construct(
        public ProductService $productService,
        public EntityManagerInterface $entityManager,
        public SerializerInterface $serializer,
        public JWTTokenManagerInterface $JWTManager,
    ) {
    }

    /**
     * @throws DateMalformedStringException|JWTDecodeFailureException
     */
    public function store(Request $request, ValidatorInterface $validator): Response
    {
        $response = $this->productService->store($request, $validator, $this->serializer, $this->entityManager);

        return new JsonResponse([
            'status' => StatusEnum::SUCCESS->value,
            'message' => 'Product successfully created',
            'data' => json_decode($response),
        ], Response::HTTP_CREATED);
    }

    /**
     * @throws DateMalformedStringException|JWTDecodeFailureException
     */
    public function update(string $id, Request $request, ValidatorInterface $validator): Response
    {
        $response = $this->productService->update($id, $request, $validator, $this->serializer,
            $this->entityManager);

        return new JsonResponse([
            'status' => StatusEnum::SUCCESS->value,
            'message' => 'Product successfully updated',
            'data' => json_decode($response),
        ], Response::HTTP_OK);
    }
}