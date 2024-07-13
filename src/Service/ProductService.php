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

    /**
     * @throws DateMalformedStringException
     * @throws JWTDecodeFailureException
     */
    public function create(
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

        $productExist = $this->productRepository->findOneBy(['name' => $product->name, 'user' => $user]);

        if ($productExist) {
            throw new CustomException('Product already exist');
        }

        $product->createdAt = now();
        $product->updatedAt = now();

        $product->user = $user;

        $this->productRepository->create($entityManager, $product);

        return $serializer->serialize($product, 'json');
    }

    /**
     * @throws JWTDecodeFailureException
     */
    public function decodeToken(Request $request): array
    {
        $header = $request->headers->get('Authorization');
        $token = explode(" ", $header)[1];
        return $this->JWTEncoder->decode($token);
    }

    public function serializePayload(Request $request, SerializerInterface $serializer)
    {
        $data = $request->getContent();

        return $serializer->deserialize($data, Product::class, 'json');
    }

    protected function validatePayload(ValidatorInterface $validator, Product $product)
    {
        $errors = $validator->validate($product);

        if (count($errors) === 0) {
            return null;
        }

        throw new CustomException($errors[0]->getMessage());
    }
}