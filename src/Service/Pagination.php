<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Hateoas\Representation\CollectionRepresentation;
use Hateoas\Representation\PaginatedRepresentation;

class Pagination
{
    private $entityClass;
    private $route;
    private $limit;
    private $criteria = [];
    private $order = ['id' => 'DESC'];
    private $currentPage;
    private $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    public function getEntityClass()
    {
        return $this->entityClass;
    }

    public function setEntityClass($entityClass)
    {
        $this->entityClass = $entityClass;

        return $this;
    }

    public function getRoute()
    {
        return $this->route;
    }

    public function setRoute($route): void
    {
        $this->route = $route;
    }

    public function getLimit()
    {
        return $this->limit;
    }

    public function setLimit($limit)
    {
        $this->limit = $limit;

        return $this;
    }

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    public function setCurrentPage($currentPage)
    {
        $this->currentPage = $currentPage;

        return $this;
    }

    public function getCriteria(): array
    {
        return $this->criteria;
    }

    public function setCriteria($criteria)
    {
        $this->criteria = $criteria;

        return $this;
    }

    public function getOrder(): array
    {
        return $this->order;
    }

    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Get the value of manager
     */
    public function getManager() : EntityManagerInterface
    {
        return $this->manager;
    }

    public function setManager($manager)
    {
        $this->manager = $manager;

        return $this;
    }

    public function getDataClient($repoclient)
    {
        return $repoclient->findBy($this->criteria, $this->order, $this->limit, $this->getOffset());
    }
    public function getOffset()
    {
        return  $this->currentPage * $this->limit - $this->limit;
    }

    /**
     * Get paginated data
     * @return PaginatedRepresentation
     */
    public function getData($data): PaginatedRepresentation
    {

        // Get elements
        $repo = $this->manager->getRepository($this->entityClass);
        $total = count($repo->findBy($this->criteria));
        $numberOfPages = ceil($total / $this->limit);


        $paginated = new PaginatedRepresentation(
            new CollectionRepresentation($data),
            $this->route,
            array(),
            $this->currentPage,
            $this->limit,
            $numberOfPages,
            'page',
            'limit',
            true,
            $total
        );

        // Return
        return $paginated;
    }
}
