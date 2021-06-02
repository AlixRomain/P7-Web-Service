<?php

namespace App\Service;

use App\Entity\Client;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Hateoas\Configuration\Route;
use Hateoas\Representation\CollectionRepresentation;
use Hateoas\Representation\Factory\PagerfantaFactory;
use Hateoas\Representation\PaginatedRepresentation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Pagination
{

    public function getViewPaginate( $repo,$paramFetcher,$route,$param = null)
    {
        $pager = $repo->search(
            $paramFetcher->get('keyword'),
            $paramFetcher->get('order'),
            $paramFetcher->get('limit'),
            $paramFetcher->get('page'),
            $param
        );

        $pagerfantaFactory    = new  PagerfantaFactory ();
        return $pagerfantaFactory->createRepresentation(
            $pager,
            new Route( $route, array())
        );

    }


}
