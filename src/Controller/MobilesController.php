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
     * @Security(name="Bearer")
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
     * @Rest\View(serializerGroups={"Default"})
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
     *          description="Show a mobiles list",
     *       @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Mobiles"))
     *      )
     * )
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
      *
      *                 example={"description": "Strong and design", "name": "Bile Mo II"}
      *             )
      *         )
      *     ),
      *     @OA\Response(
      *         response=200,
      *         description="Mobile add in database"
      *     )
      * )
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
       * @OA\Put(
       *     path="/api/admin/mobile/{id}",
       *     summary="Update one mobile by admin",
       *     tags={"mobiles"},
       *     @OA\Parameter(
       *         description="integer",
       *         in="path",
       *         name="id",
       *         required=true,
       *         @OA\Schema(type="string"),
       *         @OA\Examples(example="int", value="1", summary="An int value.")
       *     ),
       *     @OA\Response(
       *         response=200,
       *         description="OK"
       *     )
       * )
       */
    public function putUpdateOneMobile(Mobiles $mobile, ConstraintViolationList $violations): \FOS\RestBundle\View\View
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
     *     description="",
     *     operationId="deletePet",
     *     tags={"mobiles"},
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
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid ID supplied"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Mobile not found"
     *     ),
     *     security={{"petstore_auth":{"write:pets", "read:pets"}}}
     * )
     */
    public function deleteMobilesMethod(Mobiles $mobile)
    {
        $this->em->remove($mobile);
        $this->em->flush();
    }
}

