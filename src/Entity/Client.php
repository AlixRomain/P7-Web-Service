<?php

namespace App\Entity;

use App\Repository\ClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Hateoas\Configuration\Annotation as Hateoas;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity(repositoryClass=ClientRepository::class)
 * @Hateoas\Relation(
 *     "self",
 *     href = @Hateoas\Route(
 *      "all_clients_show",
 *       parameters = { "id" = "expr(object.getId())"},
 *       absolute= true,
 *     ),
 *     exclusion = @Hateoas\Exclusion(groups={"Default","MediumClients"})
 * )
 * Ici l'explusion permet de faire apparaître le link dans les groupes default et Mediumclients
 * @Hateoas\Relation(
 *     "self",
 *     href = @Hateoas\Route(
 *      "client_show",
 *       parameters = { "id" = "expr(object.getId())"},
 *       absolute= true
 *      )
 * )
 *
 */
class Client
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Serializer\Groups("MediumClients")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Serializer\Groups("MediumClients")
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     * @Serializer\Groups("MediumClients")
     */
    private $adress;

    /**
     * @ORM\OneToMany(targetEntity=User::class, mappedBy="client")
     * @Serializer\Groups("FullClients")
     */
    private $users;

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
