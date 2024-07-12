<?php

namespace App\Service;

use App\Entity\Product;
use App\Enum\StatusEnum;
use App\Repository\ProductRepository;
use DateMalformedStringException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use function Symfony\Component\Clock\now;

class ProductService
{
    public function __construct(public ProductRepository $productRepository)
    {
    }

    /**
     * @throws DateMalformedStringException
     */
    public function create(
        Request $request,
        ValidatorInterface $validator,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
    ): array {
        $product = $this->serializePayload($request, $serializer);

        $validationResponse = $this->validatePayload($validator, $product);

        if (isset($validationResponse['status']) && $validationResponse['status'] === StatusEnum::ERROR->value) {
            return [
                'status' => $validationResponse['status'],
                'message' => $validationResponse['message'],
            ];
        }

        $productExist = $this->productRepository->findOneBy(['name' => $product->name]);

        if ($productExist) {
            return [
                'status' => StatusEnum::ERROR->value,
                'message' => 'Product already exist',
            ];
        }

        $product->createdAt = now();
        $product->updatedAt = now();

        $this->productRepository->create($entityManager, $product);

        $data = $serializer->serialize($product, 'json');

        return [
            'status' => StatusEnum::SUCCESS->value,
            'message' => 'Product successfully added',
            'data' => $data,
        ];
    }

    public function serializePayload(Request $request, SerializerInterface $serializer)
    {
        $data = $request->getContent();

        return $serializer->deserialize($data, Product::class, 'json');
    }

    protected function validatePayload(ValidatorInterface $validator, Product $product): ?array
    {
        $errors = $validator->validate($product);

        if (count($errors) === 0) {
            return null;
        }

        return [
            'status' => StatusEnum::ERROR->value,
            'message' => $errors[0]->getMessage(),
        ];
    }
}