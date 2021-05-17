<?php

namespace App\Controller;

use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ClientsController extends AbstractFOSRestController
{
    private $em;
    private $repoClients;

    public function __construct( EntityManagerInterface $em, ClientRepository  $repoClients){
        $this->em = $em;
        $this->repoClients = $repoClients;
    }
    /**
     * @Route("/clients", name="clients")
     */
    public function index(): Response
    {
        return $this->render('clients/index.html.twig', [
            'controller_name' => 'ClientsController',
        ]);
    }
}
