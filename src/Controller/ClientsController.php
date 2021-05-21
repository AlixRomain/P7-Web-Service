<?php

namespace App\Controller;

use App\Entity\Client;
use App\Exception\ResourceValidationException;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationList;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Model;

class ClientsController extends AbstractFOSRestController
{
    private $em;
    private $repoClients;

    public function __construct( EntityManagerInterface $em, ClientRepository  $repoClients){
        $this->em = $em;
        $this->repoClients = $repoClients;
    }
    /**
     * Show a clients list
     * @Rest\Get(
     *     path = "/api/clients",
     *     name = "all_clients_show",
     * )
     * @Rest\View(serializerGroups={"MediumClients"})
     * @IsGranted("ROLE_ADMIN")
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
    public function getClientsList(): array
    {
        return $this->repoClients->findAll();
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
     *          description="ID de la resource",
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
     * @Rest\View(StatusCode = 201)
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
    public function postAddOneClient(Client $client, ConstraintViolationList $violations,Request $request): \FOS\RestBundle\View\View
    {

        if(count($violations)) {
            $message = 'The JSON sent contains invalid data : ' ;

            foreach ($violations as $violation){
                $message .= sprintf(
                    "Field %s: %s",
                    $violation->getPropertyPath(),
                    $violation->getMessage()
                );
            }
            throw new ResourceValidationException($message);
            //return $this->view($violations, Response::HTTP_BAD_REQUEST);
        }
        $this->em->persist($client);
        $this->em->flush();
        return $this->view(
            $client,
            Response::HTTP_CREATED,
            [
                'Location' => $this->generateUrl('client_show', ['id' => $client->getId()])
            ]
        );
    }
    /**
     * Update one client by admin
     * @Rest\Put(
     *     path = "/api/admin/client/{id}",
     *     name = "update_client",
     * )
     * @Rest\View(StatusCode = 201)
     * @ParamConverter(
     *     "newclient",
     *      converter="fos_rest.request_body",
     *     options={
     *         "validator" = {"groups" = "Create"}
     *     }
     * )
     * @param Client                 $client
     * @param ConstraintViolationList $violations
     * @throws ResourceValidationException
     * @IsGranted("ROLE_USER")
     * @Security("client === user.getClient() || is_granted('ROLE_ADMIN')")
     * @OA\Tag(name="Clients")
     */
    public function putUpdateOneClient(Client $client, Client $newclient, ConstraintViolationList $violations): \FOS\RestBundle\View\View
    {
        if(count($violations)) {
            $message = 'The JSON sent contains invalid data : ' ;

            foreach ($violations as $violation){
                $message .= sprintf(
                    "Field %s: %s",
                    $violation->getPropertyPath(),
                    $violation->getMessage()
                );
            }
            throw new ResourceValidationException($message);
            //return $this->view($violations, Response::HTTP_BAD_REQUEST);
        }

        $client->setName($newclient->getName());
        $client->setAdress($newclient->getAdress());

        $this->em->persist($client);
        $this->em->flush();
        return $this->view(
            $client,
            Response::HTTP_CREATED,
            [
                'Location' => $this->generateUrl('client_show', ['id' => $client->getId()])
            ]
        );
    }

    /**
     * Delete one client by admin
     * @Rest\Delete(
     *     path = "/api/admin/client/{id}",
     *     name = "delete_client",
     *     requirements={"id"="\d+"}
     * )
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
    public function deleteClientsMethod(Client $client)
    {
        $this->em->remove($client);
        $this->em->flush();
    }
}

