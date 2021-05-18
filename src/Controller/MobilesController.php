<?php

namespace App\Controller;

use App\Entity\Mobiles;
use App\Exception\ResourceValidationException;
use App\Repository\MobilesRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationList;

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
     *
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
     */
    public function deleteMobilesMethod(Mobiles $mobile)
    {
        $this->em->remove($mobile);
        $this->em->flush();
    }
}

