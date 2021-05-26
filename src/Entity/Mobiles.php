<?php

namespace App\Entity;

use App\Repository\MobilesRepository;
use Doctrine\ORM\Mapping as ORM;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=MobilesRepository::class)
 *
 * @Hateoas\Relation(
 *     "self",
 *     href = @Hateoas\Route(
 *      "all_mobiles_show",
 *       parameters = { "id" = "expr(object.getId())"},
 *       absolute= true
 *      )
 * )
 * )
 * * @Hateoas\Relation(
 *     "self",
 *     href = @Hateoas\Route(
 *      "mobile_show",
 *       parameters = { "id" = "expr(object.getId())"},
 *       absolute= true
 *      )
 * )
 *
 */
class Mobiles
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @OA\Property(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @OA\Property(type="string", nullable="false")
     * @Assert\NotBlank(groups={"Create", "Update"})
     * @var string
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(groups={"Create", "Update"})
     * @OA\Property(type="string")
     * @var string
     */
    private $description;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=0)
     * @OA\Property(type="float", nullable="false")
     * @Assert\NotBlank(groups={"Create", "Update"})
     * @Assert\Type(
     *     type="float",
     *     message="The price must be a numeric value",
     *     groups="Create"
     *      )
     * @var float
     */
    private $price;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): self
    {
        $this->price = $price;

        return $this;
    }
}
