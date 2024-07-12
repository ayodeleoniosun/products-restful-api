<?php

declare(strict_types = 1);

namespace App\Controller;

use App\Enum\StatusEnum;
use App\Service\ProductService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductController extends AbstractController
{
    public function __construct(public ProductService $productService)
    {
    }

    public function create(
        Request $request,
        ValidatorInterface $validator,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
    ): Response {
        $response = $this->productService->create($request, $validator, $serializer, $entityManager);

        if ($response['status'] === StatusEnum::ERROR->value) {
            return new JsonResponse([
                'status' => StatusEnum::ERROR->value,
                'message' => $response['message'],
            ], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse([
            'status' => StatusEnum::SUCCESS->value,
            'message' => 'Product successfully created',
            'data' => json_decode($response['data']),
        ], Response::HTTP_CREATED);
    }
}