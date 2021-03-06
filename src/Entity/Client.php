<?php

namespace App\Entity;

use App\Repository\ClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Hateoas\Configuration\Annotation as Hateoas;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Annotations as OA;

/**
 * @ORM\Entity(repositoryClass=ClientRepository::class)
 * @ORM\Entity(repositoryClass="App\Repository\ArticleRepository")
 * @Hateoas\Relation(
 *     "self",
 *     href = @Hateoas\Route(
 *      "client_show",
 *       parameters = { "id" = "expr(object.getId())"},
 *       absolute= true
 *      ),
 *     exclusion = @Hateoas\Exclusion(groups={"FullClients"})
 * )
 * Ici l'explusion permet de faire apparaître le link dans les groups default FullClients
 * @ExclusionPolicy("all")
 */

class Client
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Serializer\Groups("MediumClients","FullClients","clientUser","MediumUser")
     * @Serializer\Expose()
     */
    private $id;

    /**
     * @Serializer\Expose()
     * @ORM\Column(type="string", length=255)
     * @Serializer\Groups({"MediumClients","FullClients","clientUser"})
     * @OA\Property(type="string", nullable="false")
     * @Assert\NotBlank(groups={"Create", "Update"})
     * @Assert\Length(
     *     min = 3,
     *     max = 75,
     *     minMessage="Veuillez insérer un nom d'au moin 3 lettres ",
     *     groups={"Create", "Update"}
     * )
     * @var string
     */
    private $name;

    /**
     * @Expose()
     * @ORM\Column(type="string", length=255)
     * @Serializer\Groups("MediumClients","FullClients","clientUser","MediumUser")
     * @OA\Property(type="string", nullable="false")
     * @Assert\NotBlank(groups={"Create", "Update"})
     * @Assert\Length(
     *     min = 3,
     *     max = 105,
     *     minMessage="Veuillez insérer une adrese valide",
     *     groups={"Create", "Update"}
     * )
     * @var string
     */
    private $adress;

    /**
     * @Serializer\Expose(if="object === null")
     * @ORM\OneToMany(targetEntity=User::class, mappedBy="client")
     * @Serializer\Groups("FullClients")
     */
    private $users;


    /**
     * @var bool
     * @Serializer\Exclude()
     */
    public $exclude = true;

    /**
     * @return mixed
     */
    public function getExclude()
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

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getAdress(): ?string
    {
        return $this->adress;
    }

    public function setAdress(string $adress): self
    {
        $this->adress = $adress;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setClient($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getClient() === $this) {
                $user->setClient(null);
            }
        }

        return $this;
    }
}
