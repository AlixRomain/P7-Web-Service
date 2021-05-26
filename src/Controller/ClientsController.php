<?php

namespace App\Controller;

use App\Entity\Client;
use App\Exception\Errors;
use App\Exception\ResourceValidationException;
use App\Repository\ClientRepository;
use App\Service\Pagination;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationList;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Model;

class ClientsController extends AbstractFOSRestController
{
    private $em;
    private $repoClients;
    private $errors;

    public function __construct( EntityManagerInterface $em, ClientRepository  $repoClients, Errors $errors){
        $this->em = $em;
        $this->repoClients = $repoClients;
        $this->errors = $errors;
    }
    /**
     * Show a clients list
     * @Rest\Get(
     *     path = "/api/clients",
     *     name = "all_clients_show",
     * )
     * @Rest\View(serializerGroups={"MediumClients"})
     * @IsGranted("ROLE_ADMIN")
     * @OA\Parameter(
     *   name="page",
     *   description="The page number to show",
     *   in="query"
     * )
     * @OA\Parameter(
     *   name="limit",
     *   description="The number of mobile per page",
     *   in="query"
     * )
     * @OA\Tag(name="Clients")
     * @OA\Get(
     *      path = "/api/clients",
     *     @OA\Response(
     *       response="200",
     *       description="Show a clients list",
     *       @OA\JsonContent(
     *          type="array",
     *           @OA\Items(ref=@Model(type=Client::class, groups={"MediumClients"}))
     *       )
     *    )
     * )
     * @OA\Response(
     *     response=401,
     *     description="UNAUTHORIZED - JWT Token not found | Expired JWT Token | Invalid JWT Token"
     * )
     * @OA\Response(
     *     response=403,
     *     description="ACCESS DENIED"
     * )
     */
    public function getClientsList( Request $request, Pagination $pagination): View
    {
        $limit = $request->query->get('limit', $this->getParameter('default_client_limit'));
        $page = $request->query->get('page', 1);
        $route = $request->attributes->get('_route');
        $criteria = [];
        $pagination->setEntityClass(Client::class)
            ->setRoute($route);
        $pagination->setCurrentPage($page)
            ->setLimit($limit);
        $pagination->setCriteria($criteria);

        $paginated = $pagination->getData();

        return $this->view(
            $paginated,
            Response::HTTP_OK
        );
    }

    /**
     * Show one client details and this users
     * @Rest\Get(
     *     path = "/api/clients/{id}",
     *     name = "client_show",
     *     requirements={"id"="\d+"}
     * )
     * @Rest\View(serializerGroups={"FullClients"})
     * @IsGranted("ROLE_USER")
     * @Security("client === user.getClient() || is_granted('ROLE_ADMIN')")
     * @OA\Tag(name="Clients")
     * @OA\Get(
     *      path = "/api/clients/{id}",
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="ID client to read",
     *          required=true
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Show a client detail with users list",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref=@Model(type=Client::class, groups={"FullClients"}))
     *          )
     *      )
     * )
     * @OA\Response(
     *     response=401,
     *     description="UNAUTHORIZED - JWT Token not found | Expired JWT Token | Invalid JWT Token"
     * )
     * @OA\Response(
     *     response=404,
     *     description="NOT FOUND"
     * )
     */
    public function getOneClients(Client $client): Client
    {
        return $client;
    }

    /**
     * Add one client corporation by admin
     * @Rest\Post(
     *     path = "/api/admin/client",
     *     name = "add_client",
     *     options={
     *         "validator" = {"groups" = "Create"}
     *     }
     * )
     * @Rest\View(StatusCode = 201, serializerGroups={"MediumClients"})
     * @ParamConverter("client",converter="fos_rest.request_body")
     * @throws ResourceValidationException
     * @IsGranted("ROLE_ADMIN")
     * @OA\Post(
     *     path="/api/admin/client",
     *     summary=" Add one client corporation by admin",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="adress",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="name",
     *                     type="string"
     *                 ),
     *                 example={"name":"free","adress":"7800 rue de platanes à Tourcoin"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *        response=201,
     *        description="CREATED",
     *        @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Client")),
     *        @OA\Schema(
     *          type="array",
     *          @OA\Items(ref=@Model(type=Client::class))
     *        )
     *     )
     * )
     * @OA\Response(
     *     response=400,
     *     description="BAD REQUEST"
     * )
     * @OA\Response(
     *     response=401,
     *     description="UNAUTHORIZED - JWT Token not found | Expired JWT Token | Invalid JWT Token"
     * )
     * @OA\Response(
     *     response=403,
     *     description="ACCESS DENIED"
     * )
     * @OA\Tag(name="Clients")
     */
    public function postAddOneClient(Client $client, ConstraintViolationList $violations,Request $request): View
    {
        $this->errors->violation($violations);
        $this->em->persist($client);
        $this->em->flush();
        return $this->view(
            $client,
            Response::HTTP_CREATED
        );
    }

    /**
     * Update one client by admin
     * @Rest\Put(
     *     path = "/api/client/{id}",
     *     name = "update_client",
     * )
     * @Rest\View(StatusCode = 201, serializerGroups={"MediumClients"})
     * @ParamConverter(
     *     "newclient",
     *      converter="fos_rest.request_body",
     *     options={
     *         "validator" = {"groups" = "Update"}
     *     }
     * )
     *
     * @param Client                  $client
     * @param Client                  $newclient
     * @param ConstraintViolationList $violations
     *
     * @return View
     * @throws ResourceValidationException
     * @IsGranted("ROLE_USER")
     * @Security("client === user.getClient() || is_granted('ROLE_ADMIN')")
     * @OA\Tag(name="Clients")
     * @OA\Put(
     *     path="/api/client/{id}",
     *     summary="Update one client by admin",
     *      @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="name",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="description",
     *                     type="string"
     *                 ),
     *                 example={"name":"New name","adress":"New Description"}
     *             )
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Id client to update",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         @OA\Examples(example="int", value="1", summary="An int value.")
     *     ),
     *    @OA\Response(
     *        response=201,
     *        description="CREATED",
     *        @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Client")),
     *        @OA\Schema(
     *          type="array",
     *          @OA\Items(ref=@Model(type=Client::class))
     *        )
     *     )
     * )
     * @OA\Response(
     *     response=400,
     *     description="BAD REQUEST"
     * )
     * @OA\Response(
     *     response=401,
     *     description="UNAUTHORIZED - JWT Token not found | Expired JWT Token | Invalid JWT Token"
     * )
     * @OA\Response(
     *     response=403,
     *     description="ACCESS DENIED"
     * )
     */
    public function putUpdateOneClient(Client $client, Client $newclient, ConstraintViolationList $violations,Request $request): View
    {
        $data = json_decode($request->getContent(),true);
        $this->errors->violation($violations);
        if(array_key_exists('name', $data)){$client->setName($newclient->getName());}
        if(array_key_exists('adress', $data)){$client->setAdress($newclient->getAdress());}

        $this->em->persist($client);
        $this->em->flush();
        return $this->view(
            $client,
            Response::HTTP_CREATED
        );
    }

    /**
     * Delete one client by admin
     * @Rest\Delete(
     *     path = "/api/admin/client/{id}",
     *     name = "delete_client",
     *     requirements={"id"="\d+"}
     * )
     *
     * @param Client $client
     *
     * @return JsonResponse
     * @Rest\View(StatusCode = 204)
     * @IsGranted("ROLE_ADMIN")
     * @OA\Tag(name="Clients")
     * @OA\Delete(
     *     path="/api/admin/client/{id}",
     *     summary="Delete one client by admin",
     *     description="DELETE",
     *     operationId="delete Client",
     *     @OA\Parameter(
     *         description="Client id to delete",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Header(
     *         header="api_key",
     *         description="Api key header",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     )
     * )
     * @OA\Response(
     *     response=204,
     *     description="NO CONTENT"
     * )
     * @OA\Response(
     *     response=401,
     *     description="UNAUTHORIZED - JWT Token not found | Expired JWT Token | Invalid JWT Token"
     * )
     * @OA\Response(
     *     response=403,
     *     description="ACCESS DENIED"
     * )
     * @OA\Response(
     *     response=404,
     *     description="NOT FOUND"
     * )
     */
    public function deleteClientsMethod(Client $client): JsonResponse
    {
        $this->em->remove($client);
        $this->em->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}

