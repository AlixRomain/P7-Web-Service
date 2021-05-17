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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
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
     *     requirements={"id"="\d+"}
     * )
     * @Rest\View(serializerGroups={"Default"})
     * @IsGranted("ROLE_USER")
     */
    public function getMobilesList(Mobiles $mobiles): Mobiles
    {
        return $mobiles;
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
      *     path = "/api/admin/mobiles",
      *     name = "add_mobile",
      * )
      * @Rest\View(StatusCode = 201)
      * @ParamConverter(
      *     "mobiles",
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
                'Location' => $this->generateUrl('tools_show', ['id' => $mobile->getId()])
            ]
        );
    }
      /**
       * Update one mobile by admin
      * @Rest\Put(
      *     path = "/api/admin/mobiles/{id}",
      *     name = "update_mobile",
      * )
      * @Rest\View(StatusCode = 201)
      * @ParamConverter(
      *     "mobiles",
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
                'Location' => $this->generateUrl('tools_show', ['id' => $mobile->getId()])
            ]
        );
    }

    /**
     * Delete one mobile by admin
     * @Rest\Delete(
     *     path = "/api/delete-tools/{id}",
     *     name = "tools_delete",
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

