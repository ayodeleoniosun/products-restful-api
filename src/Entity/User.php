<?php

namespace App\Entity;

use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Mapping\ClassMetadata;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements PasswordAuthenticatedUserInterface, UserInterface
{
    #[ORM\Column(length: 255)]
    public string $password;

    #[ORM\Column]
    public DateTimeImmutable $createdAt;

    #[ORM\Column]
    public DateTimeImmutable $updatedAt;
    #[ORM\Column(length: 255)]
    public string $email;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public ?int $id = null;
    #[ORM\Column(length: 255)]
    private string $firstname;
    #[ORM\Column(length: 255)]
    private string $lastname;
    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $deletedAt = null;

    /**
     * @var Collection<int, Product>
     */
    /**
     * @ORM\OneToMany(targetEntity=Product::class, mappedBy="user")
     */

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

    public function getRoles(): array
    {
        return [];
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials(): void
    {
    }

    /**
     * Returns the identifier for this user (e.g. username or email address).
     */
    public function getUserIdentifier(): string
    {
        return '';
    }
}
