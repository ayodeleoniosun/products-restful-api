<?php

namespace App\Service;

use App\Entity\Product;
use App\Exception\CustomException;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use DateMalformedStringException;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use function Symfony\Component\Clock\now;

class ProductService
{
    public function __construct(
        public ProductRepository $productRepository,
        public UserRepository $userRepository,
        public JWTEncoderInterface $JWTEncoder,
    ) {
    }

    public function index(Request $request, SerializerInterface $serializer): string
    {
        $products = $this->productRepository->findAll();

        return $serializer->serialize($products, 'json');
    }

    public function show(string $id, Request $request, SerializerInterface $serializer): string
    {
        $product = $this->productRepository->findRecordBy(compact('id'));

        if (!$product) {
            throw new CustomException('Product not found', Response::HTTP_NOT_FOUND);
        }

        return $serializer->serialize($product, 'json');
    }

    /**
     * @throws DateMalformedStringException
     * @throws JWTDecodeFailureException
     */
    public function store(
        Request $request,
        ValidatorInterface $validator,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
    ): string {
        $token = $this->decodeToken($request);
        $user = $this->userRepository->find($token['user_id']);

        $product = $this->serializePayload($request, $serializer);
        $product->name = strtolower($product->name);
        $product->description = strtolower($product->description);

        $this->validatePayload($validator, $product);

        $productExist = $this->productRepository->findRecordBy(['name' => $product->name, 'user' => $user]);

        if ($productExist) {
            throw new CustomException('Product already exist');
        }

        $product->createdAt = now();
        $product->updatedAt = now();

        $product->user = $user;

        $this->productRepository->createOrUpdate($entityManager, $product);

        return $serializer->serialize($product, 'json');
    }

    /**
     * @param  Request  $request
     * @return array<string>
     * @throws JWTDecodeFailureException
     */
    public function decodeToken(Request $request): array
    {
        $header = $request->headers->get('Authorization');
        $token = explode(" ", $header)[1];
        return $this->JWTEncoder->decode($token);
    }

    public function serializePayload(Request $request, SerializerInterface $serializer): Product
    {
        $data = $request->getContent();

        return $serializer->deserialize($data, Product::class, 'json');
    }

    protected function validatePayload(ValidatorInterface $validator, Product $product): CustomException|null
    {
        $errors = $validator->validate($product);

        if (count($errors) === 0) {
            return null;
        }

        throw new CustomException($errors[0]->getMessage());
    }

    /**
     * @throws DateMalformedStringException
     * @throws JWTDecodeFailureException
     */

    public function update(
        string $id,
        Request $request,
        ValidatorInterface $validator,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
    ): string {
        $token = $this->decodeToken($request);
        $user = $this->userRepository->find($token['user_id']);

        $getProduct = $this->productRepository->findRecordBy(compact('id', 'user'));

        if (!$getProduct) {
            throw new CustomException('Product not found', Response::HTTP_NOT_FOUND);
        }

        $product = $this->serializePayload($request, $serializer);
        $getProduct->name = strtolower($product->name);
        $getProduct->description = strtolower($product->description);

        $this->validatePayload($validator, $getProduct);

        $productExist = $this->productRepository->findOneNotById($id, ['name' => $getProduct->name, 'user' => $user]);

        if ($productExist) {
            throw new CustomException('Product already exist');
        }

        $getProduct->updatedAt = now();
        $getProduct->id = $id;

        $this->productRepository->createOrUpdate($entityManager, $getProduct, 'update');

        return $serializer->serialize($getProduct, 'json');
    }

    /**
     * @throws DateMalformedStringException
     * @throws JWTDecodeFailureException
     */
    public function delete(
        string $id,
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
    ): string {
        $token = $this->decodeToken($request);
        $user = $this->userRepository->find($token['user_id']);

        $getProduct = $this->productRepository->findRecordBy(compact('id', 'user'));

        if (!$getProduct) {
            throw new CustomException('Product not found', Response::HTTP_NOT_FOUND);
        }

        $getProduct->deletedAt = now();
        $getProduct->id = $id;

        $this->productRepository->createOrUpdate($entityManager, $getProduct, 'update');

        return $serializer->serialize($getProduct, 'json');
    }
}
