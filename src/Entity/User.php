<?php

namespace App\Entity;

use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Mapping\ClassMetadata;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements PasswordAuthenticatedUserInterface
{
    #[ORM\Column(length: 255)]
    public ?string $password = null;

    #[ORM\Column]
    public ?DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    public ?DateTimeImmutable $updatedAt = null;
    #[ORM\Column(length: 255)]
    public ?string $email = null;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    #[ORM\Column(length: 255)]
    private ?string $firstname = null;
    #[ORM\Column(length: 255)]
    private ?string $lastname = null;
    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $deletedAt = null;

    /**
     * @var Collection<int, Product>
     */
    #[ORM\OneToMany(targetEntity: Product::class, mappedBy: 'userId')]
    private Collection $products;

    public function __construct()
    {
        $this->products = new ArrayCollection();
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addPropertyConstraints('firstname', [
            new NotBlank([
                'message' => 'Firstname cannot be blank.',
            ]),
            new Length([
                'min' => 3,
                'max' => 20,
                'minMessage' => 'Firstname must not be less than 3 characters.',
                'maxMessage' => 'Firstname must not be more than 20 characters.',
            ]),
        ])->addPropertyConstraints('lastname', [
            new NotBlank([
                'message' => 'Lastname cannot be blank.',
            ]),
            new Length([
                'min' => 3,
                'max' => 20,
                'minMessage' => 'Lastname must not be less than 3 characters',
                'maxMessage' => 'Lastname must not be more than 20 characters',
            ]),
        ])->addPropertyConstraints('email', [
            new NotBlank([
                'message' => 'Email address cannot be blank.',
            ]),
            new Email([
                'message' => 'Invalid email address supplied.',
            ]),
        ])->addPropertyConstraints('password', [
            new NotBlank([
                'message' => 'Password cannot be blank.',
            ]),
            new Length([
                'min' => 8,
                'minMessage' => 'Password must not be less than 8 characters.',
            ]),
        ]);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getDeletedAt(): ?DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?DateTimeImmutable $deletedAt): static
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * @return Collection<int, Product>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): static
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
            $product->setUserId($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): static
    {
        if ($this->products->removeElement($product)) {
            // set the owning side to null (unless already changed)
            if ($product->getUserId() === $this) {
                $product->setUserId(null);
            }
        }

        return $this;
    }
}
