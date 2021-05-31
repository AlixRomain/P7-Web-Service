<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Mobile;
use App\Entity\Mobiles;
use App\Exception\Errors;
use App\Exception\ResourceValidationException;
use App\Repository\MobilesRepository;
use App\Service\Pagination;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Hateoas\Configuration\Route;
use Hateoas\HateoasBuilder;
use Hateoas\Representation\CollectionRepresentation;
use Hateoas\Representation\Factory\PagerfantaFactory;
use Hateoas\Representation\PaginatedRepresentation;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations\Parameter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationList;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Model;


class MobilesController extends AbstractFOSRestController
{
    private $em;
    private $repoMobiles;
    private $errors;
    private $hateoas;

    public function __construct( EntityManagerInterface $em, MobilesRepository $repoMobiles, Errors $errors){
        $this->em = $em;
        $this->repoMobiles = $repoMobiles;
        $this->errors = $errors;
        $this->hateoas = HateoasBuilder::create()->build();
    }

    /**
     * Show a mobiles list
     * @Rest\Get(
     *     path = "/api/mobiles",
     *     name = "all_mobiles_show",
     * )
     * @Rest\View()
     * @IsGranted("ROLE_USER")
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
     * @OA\Parameter(
     *   name="name",
     *   description="The mobile name to search",
     *   in="query"
     * )
     * @OA\Get(
     *      path = "/api/mobiles",
     *     @OA\Response(
     *       response="200",
     *       description="Show a mobiles list",
     *       @OA\JsonContent(
     *          type="array",
     *           @OA\Items(ref=@Model(type=Mobiles::class))
     *       )
     *    )
     * )
     * @OA\Tag(name="Mobiles")
     * @OA\Response(
     *     response=401,
     *     description="UNAUTHORIZED - JWT Token not found | Expired JWT Token | Invalid JWT Token"
     * )
     * @Rest\QueryParam(
     *     name="keyword",
     *     requirements="[a-zA-Z0-9]",
     *     nullable=true,
     *     description="The keyword to search for."
     * )
     * @Rest\QueryParam(
     *     name="order",
     *     requirements="asc|desc",
     *     default="asc",
     *     description="Sort order (asc or desc)"
     * )
     * @Rest\QueryParam(
     *     key="limit",
     *     name="limit",
     *     requirements="\d+",
     *     default="5",
     *     description="Max number of movies per page."
     * )
     * @Rest\QueryParam(
     *     name="page",
     *     requirements="\d+",
     *     key="page",
     *     default="1",
     *     description="The current page"
     * )
     */
    public function getMobilesList(ParamFetcherInterface $paramFetcher)
    {
        $pager = $this->getDoctrine()->getRepository(Mobiles::class)->search(
            $paramFetcher->get('keyword'),
            $paramFetcher->get('order'),
            $paramFetcher->get('limit'),
            $paramFetcher->get('page')
        );

        $pagerfantaFactory   = new  PagerfantaFactory ();
        $paginatedCollection = $pagerfantaFactory->createRepresentation(
            $pager,
            new Route( 'all_mobiles_show', array())
        );

        $view = $this->view(
            $paginatedCollection,
            Response::HTTP_OK
        );
        return $this->handleView($view);
    }

    /**
     * Show one mobile details
     * @Rest\Get(
     *     path = "/api/mobiles/{id}",
     *     name = "mobile_show",
     *     requirements={"id"="\d+"}
     * )
     * @Rest\View(statusCode= 200)
     * @IsGranted("ROLE_USER")
     * @OA\Get(
     *      path = "/api/mobiles/{id}",
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="ID mobile to read",
     *          required=true
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Show a mobile detail",
     *       @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Mobiles"))
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
     * @OA\Tag(name="Mobiles")
     */
    public function getOneMobiles(Mobiles $mobile): Mobiles
    {
        return $mobile;
    }

     /**
      * Add one mobile by admin
      * @Rest\Post(
      *     path = "/api/admin/mobile",
      *     name = "add_mobile",
      * )
      * @Rest\View(StatusCode = 201)
      * @ParamConverter(
      *     "mobile",
      *     converter="fos_rest.request_body",
      *     options={
      *         "validator" = {"groups" = "Create"}
      *     })
      * @throws ResourceValidationException
      * @IsGranted("ROLE_ADMIN")
      * @OA\Post(
      *     path="/api/admin/mobile",
      *     summary="Add one mobile by admin",
      *     @OA\RequestBody(
      *         @OA\MediaType(
      *             mediaType="application/json",
      *             @OA\Schema(
      *                 @OA\Property(
      *                     property="description",
      *                     type="string"
      *                 ),
      *                 @OA\Property(
      *                     property="name",
      *                     type="string"
      *                 ),
      *                 @OA\Property(
      *                     property="price",
      *                     type="float"
      *                 ),
      *                 example={"name":"Frejus VI","description":"il est cool et pas chère!","price": 148}
      *             )
      *         )
      *     ),
      *     @OA\Response(
      *        response=201,
      *        description="CREATED",
      *        @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Mobiles")),
      *        @OA\Schema(
      *          type="array",
      *          @OA\Items(ref=@Model(type=Mobiles::class))
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
      * @OA\Tag(name="Mobiles")
     */
    public function postAddOneMobile(Mobiles $mobile, ConstraintViolationList $violations): View
    {
        $this->errors->violation($violations);
        $this->em->persist($mobile);
        $this->em->flush();
        return $this->view(
            $mobile,
            Response::HTTP_CREATED,
            [
                'Location' => $this->generateUrl('mobile_show', ['id' => $mobile->getId()])
            ]
        );
    }

    /**
     * Update one mobile by admin
     * @Rest\Put(
     *     path = "/api/admin/mobile/{id}",
     *     name = "update_mobile",
     *    requirements={"id"="\d+"}
     * )
     * @Rest\View(StatusCode = 201)
     * @ParamConverter(
     *     "newMobile",
     *      converter="fos_rest.request_body",
     *      options={
     *         "validator" = {"groups" = "Update"}
     *     }
     * )
     *
     * @param Mobiles                 $mobile
     * @param Mobiles                 $newMobile
     * @param ConstraintViolationList $violations
     *
     * @return View
     * @throws ResourceValidationException
     * @IsGranted("ROLE_ADMIN")
     * @OA\Put(
     *     path="/api/admin/mobile/{id}",
     *     summary="Update one mobile by admin",
     *      @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="description",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="name",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="price",
     *                     type="float"
     *                 ),
     *                 example={"name":"New name","description":"New Description","price": 0}
     *             )
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Id mobiles to update",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         @OA\Examples(example="int", value="1", summary="An int value.")
     *     ),
     *    @OA\Response(
     *        response=201,
     *        description="CREATED",
     *        @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Mobiles")),
     *        @OA\Schema(
     *          type="array",
     *          @OA\Items(ref=@Model(type=Mobiles::class))
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
     * @OA\Tag(name="Mobiles")
     */
    public function putUpdateOneMobile(Mobiles $mobile, Mobiles $newMobile, ConstraintViolationList $violations, Request $request): View
    {
        $data = json_decode($request->getContent(),true);
        $this->errors->violation($violations);
        if(array_key_exists('description', $data)){$mobile->setDescription($newMobile->getDescription());}
        if(array_key_exists('name', $data)){$mobile->setName($newMobile->getName());}
        if(array_key_exists('price', $data)){$mobile->setPrice($newMobile->getPrice());}
        $this->em->flush();
        return $this->view(
            $mobile,
            Response::HTTP_CREATED,
            [
                'Location' => $this->generateUrl('mobile_show', ['id' => $mobile->getId()])
            ]
        );
    }

    /**
     * Delete one mobile by admin
     * @Rest\Delete(
     *     path = "/api/admin/mobile/{id}",
     *     name = "delete_mobile",
     *     requirements={"id"="\d+"}
     * )
     * @Rest\View(StatusCode = 204)
     * @IsGranted("ROLE_ADMIN")
     * @OA\Delete(
     *     path="/api/admin/mobile/{id}",
     *     summary="Delete one mobile by admin",
     *     description="DELETE",
     *     operationId="delete Mobile",
     *     @OA\Parameter(
     *         description="Mobile id to delete",
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
     * @OA\Tag(name="Mobiles")
     */
    public function deleteMobilesMethod(Mobiles $mobile)
    {
        $this->em->remove($mobile);
        $this->em->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}

