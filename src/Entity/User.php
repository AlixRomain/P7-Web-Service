<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use JMS\Serializer\Annotation\ExclusionPolicy;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use OpenApi\Annotations as OA;
use Hateoas\Configuration\Annotation as Hateoas;
use Symfony\Component\Validator\Constraints as Assert;



/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Entity(repositoryClass="App\Repository\ArticleRepository")
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
 * @Hateoas\Relation(
 *     "self",
 *     href = @Hateoas\Route(
 *      "user_show",
 *       parameters = { "id" = "expr(object.getId())"},
 *       absolute= true,
 *     ),
 *     exclusion = @Hateoas\Exclusion(groups={"MediumUser"})
 * )
 * @ExclusionPolicy("all")
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
     * @Serializer\Expose()
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
     * @Serializer\Expose()
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     * @Serializer\Groups("FullClients")
     * @Serializer\Expose()
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
     * @Serializer\Expose(if="!object")
     */
    private $password;

    /**
     * @ORM\Column(type="datetime")
     * @Serializer\Groups("FullClients","MediumUser")
     * @Serializer\Expose()
     */
    private $createdAt;

    /**
     * @ORM\Column(type="string", length=255)
     * @OA\Property(type="string", maxLength=255)
     */
    private $username;

    /**
     * @ORM\Column(type="integer")
     * @OA\Property(type="integer", nullable="false")
     * @Serializer\Groups("FullClients","MediumUser")
     * @Assert\NotBlank(groups={"Create", "Update"})
     * @Assert\Regex(
     *     pattern="/^([0-9])+$/",
     *     groups={"Create", "Update"}
     *     )
     * @Serializer\Expose()
     */
    private $age;

    /**
     * @Serializer\Expose(if="object !== !object")
     * @ORM\ManyToOne(targetEntity=Client::class, inversedBy="users")
     * @Serializer\Groups("clientUser", "MediumUser")
     */
    private $client;

    /**
     *
     * @ORM\Column(type="string", length=255)
     * @Serializer\Groups("FullClients","MediumUser")
     * @Assert\NotBlank(groups={"Create", "Update"})
     * @Assert\Length(
     *     min = 3,
     *     max = 75,
     *     minMessage="Veuillez insérer un nom d'au moin 3 lettres ",
     *     groups={"Create", "Update"}
     * )
     * @Serializer\Expose()
     */
    private $fullname;


    /**
     * @var bool
     * @Serializer\Exclude()
     */
    private $exclude = false;

    /**
     * @return bool
     */
    public function getExclude(): bool
    {
        return $this->exclude;
    }

    /**
     * @param mixed $exclude
     */
    public function setExclude($exclude): void
    {
        $this->exclude = $exclude;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
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

    public function getFullname(): ?string
    {
        return $this->fullname;
    }

    public function setFullname(string $fullname): self
    {
        $this->fullname = $fullname;

        return $this;
    }
}

