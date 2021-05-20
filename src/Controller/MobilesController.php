<?php

namespace App\Controller;

use App\Entity\Mobiles;
use App\Exception\ResourceValidationException;
use App\Repository\MobilesRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationList;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Model;


class MobilesController extends AbstractFOSRestController
{
    private $em;
    private $repoMobiles;

    public function __construct( EntityManagerInterface $em, MobilesRepository $repoMobiles){
        $this->em = $em;
        $this->repoMobiles = $repoMobiles;
    }

    /**
     * Show a mobiles list
     * @Rest\Get(
     *     path = "/api/mobiles",
     *     name = "all_mobiles_show",
     * )
     * @Rest\View(serializerGroups={"Default"})
     * @IsGranted("ROLE_USER")
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
     */
    public function getMobilesList(): array
    {
        return $this->repoMobiles->findAll();
    }

    /**
     * Show one mobile details
     * @Rest\Get(
     *     path = "/api/mobiles/{id}",
     *     name = "mobile_show",
     *     requirements={"id"="\d+"}
     * )
     * @Rest\View(statusCode= 200 ,serializerGroups={"Default"})
     * @IsGranted("ROLE_USER")
     * @OA\Get(
     *      path = "/api/mobiles/{id}",
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="ID de la resource",
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
      *      converter="fos_rest.request_body",
      *      options={
      *         "validator" = {"groups" = "Create"}
      *     }
      * )
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
    public function postAddOneMobile(Mobiles $mobile, ConstraintViolationList $violations): \FOS\RestBundle\View\View
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
     *         "validator" = {"groups" = "Create"}
     *     }
     * )
     *
     * @param Mobiles                 $mobile
     * @param ConstraintViolationList $violations
     *
     * @return \FOS\RestBundle\View\View
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
     *         description="integer",
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
    public function putUpdateOneMobile(Mobiles $mobile, Mobiles $newMobile, ConstraintViolationList $violations): \FOS\RestBundle\View\View
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
        $mobile->setDescription($newMobile->getDescription());
        $mobile->setName($newMobile->getName());
        $mobile->setPrice($newMobile->getPrice());
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
    }
}

