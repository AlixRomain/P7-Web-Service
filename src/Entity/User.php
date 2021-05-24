<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use OpenApi\Annotations as OA;
use Hateoas\Configuration\Annotation as Hateoas;
use Symfony\Component\Validator\Constraints as Assert;



/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity("email",
 *     message="Cette adresse email est déjà inséré en base",
 *     groups="Create"
 * )
 * @ORM\Table(name="`user`")
 * @Hateoas\Relation(
 *     "self",
 *     href = @Hateoas\Route(
 *      "all_users_show_admin",
 *       parameters = { "id" = "expr(object.getId())"},
 *       absolute= true,
 *     ),
 *     exclusion = @Hateoas\Exclusion(groups={"clientUser"})
 * )
 * Ici l'explusion permet de faire apparaître le link dans les groupes default et Mediumclients
 */
class User implements UserInterface
{
    const ROLE_ADMIN  = ["ROLE_ADMIN","ROLE_USER", "ROLE_CLIENT"];
    const ROLE_USER = ["ROLE_USER", "ROLE_CLIENT"];
    const ROLE_CLIENT  = ["ROLE_CLIENT"];
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @var int
     * @OA\Property(description="The unique identifier of the user.")
     * @Serializer\Groups("FullClients","MediumUser")
     */

    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Serializer\Groups("FullClients","MediumUser")
     * @Assert\Email(groups="Create")
     * @Assert\NotBlank(groups="Create")
     * @Assert\Length(
     *     max = 255
     * )
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     * @Serializer\Groups("FullClients")
     */
    private $roles = self::ROLE_CLIENT;

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     * @Assert\NotBlank(groups="Create")
     * @Assert\Length(
     *     min = 8,
     *     max = 255,
     *     groups="Create"
     * )
     * @Assert\Regex(
     *     groups="Create",
     *     pattern = "^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*\W)^",
     *     match = true,
     *     message = "Password must contain at least one lowercase, one uppercase, one digit and one special character !"
     * )
     */
    private $password;

    /**
     * @ORM\Column(type="datetime")
     * @Serializer\Groups("FullClients","MediumUser")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="string", length=255)
     * @OA\Property(type="string", maxLength=255)
     * @Serializer\Groups("FullClients","MediumUser")
     * @Assert\NotBlank(groups="Create")
     * @Assert\Length(
     *     min = 3,
     *     max = 75,
     *     minMessage="Veuillez insérer un nom d'au moin 3 lettres ",
     *     groups="Create"
     * )
     */
    private $username;

    /**
     * @ORM\Column(type="integer")
     * @OA\Property(type="integer", nullable="false")
     * @Serializer\Groups("FullClients","MediumUser")
     * @Assert\NotBlank(groups="Create")
     * @Assert\Regex(
     *     pattern="/^([0-9])+$/",
     *     groups="Create"
     *     )
     */
    private $age;

    /**
     * @ORM\ManyToOne(targetEntity=Client::class, inversedBy="users")
     * @Serializer\Groups("clientUser")
     */
    private $client;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function setAge(int $age): self
    {
        $this->age = $age;

        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): self
    {
        $this->client = $client;

        return $this;
    }
}
