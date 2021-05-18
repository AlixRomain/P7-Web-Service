<?php

namespace App\Entity;

use App\Repository\MobilesRepository;
use Doctrine\ORM\Mapping as ORM;
use Hateoas\Configuration\Annotation as Hateoas;

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
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $description;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=0)
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
