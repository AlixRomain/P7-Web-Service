<?php

namespace App\Controller;

use App\Entity\User;
use App\Exception\ResourceValidationException;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationList;

class UserController extends AbstractFOSRestController
{
    private $em;
    private $repoUser;

    public function __construct(EntityManagerInterface $em, UserRepository $repoUser)
    {
        $this->em          = $em;
        $this->repoUser = $repoUser;
    }

    /**
     * Show a customer list from one clients coporation
     * @Rest\Get(
     *     path = "/api/users",
     *     name = "all_users_show",
     * )
     * @Rest\View(serializerGroups={"Default"})
     * @IsGranted("ROLE_USER")
     */
    public function getUserList(User $users): User
    {
        return $users;
    }

    /**
     * Show one user details
     * @Rest\Get(
     *     path = "/api/users/{id}",
     *     name = "user_show",
     *     requirements={"id"="\d+"}
     * )
     * @Rest\View(serializerGroups={"Default"})
     * @IsGranted("ROLE_USER")
     */
    public function getOneUser(User $user): User
    {
        return $user;
    }
/**
     * Show all users from every customers corporation
     * @Rest\Get(
     *     path = "/api/admin/users",
     *     name = "all_users_show"
     * )
     * @Rest\View(serializerGroups={"Default"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function getAllUsersOfBileMo(User $users): User
    {
        return $users;
    }

    /**
     * Add one user by client or admin
     * @Rest\Post(
     *     path = "/api/user",
     *     name = "add_user",
     * )
     * @Rest\View(StatusCode = 201)
     * @ParamConverter(
     *     "user",
     *      converter="fos_rest.request_body",
     *      options={
     *         "validator" = {"groups" = "Create"}
     *     }
     * )
     * @throws ResourceValidationException
     * @IsGranted("ROLE_USER")
     */
    public function postAddOneUser(User $user, ConstraintViolationList $violations): \FOS\RestBundle\View\View
    {
        if (count($violations)) {
            $message = 'The JSON sent contains invalid data : ';

            foreach ($violations as $violation) {
                $message .= sprintf(
                    "Field %s: %s",
                    $violation->getPropertyPath(),
                    $violation->getMessage()
                );
            }
            throw new ResourceValidationException($message);
            //return $this->view($violations, Response::HTTP_BAD_REQUEST);
        }
        $this->em->persist($user);
        $this->em->flush();
        return $this->view(
            $user,
            Response::HTTP_CREATED,
            [
                'Location' => $this->generateUrl('tools_show', ['id' => $user->getId()])
            ]
        );
    }

    /**
     * Update one user by admin
     * @Rest\Put(
     *     path = "/api/user/{id}",
     *     name = "update_user",
     * )
     * @Rest\View(StatusCode = 201)
     * @ParamConverter(
     *     "user",
     *      converter="fos_rest.request_body",
     *      options={
     *         "validator" = {"groups" = "Create"}
     *     }
     * )
     * @throws ResourceValidationException
     * @IsGranted("ROLE_USER")
     */
    public function putUpdateOneUser(User $user, ConstraintViolationList $violations): \FOS\RestBundle\View\View
    {
        if (count($violations)) {
            $message = 'The JSON sent contains invalid data : ';

            foreach ($violations as $violation) {
                $message .= sprintf(
                    "Field %s: %s",
                    $violation->getPropertyPath(),
                    $violation->getMessage()
                );
            }
            throw new ResourceValidationException($message);
            //return $this->view($violations, Response::HTTP_BAD_REQUEST);
        }
        $this->em->persist($user);
        $this->em->flush();
        return $this->view(
            $user,
            Response::HTTP_CREATED,
            [
                'Location' => $this->generateUrl('tools_show', ['id' => $user->getId()])
            ]
        );
    }

    /**
     * Delete one user by admin
     * @Rest\Delete(
     *     path = "/api/user/{id}",
     *     name = "delete_user",
     *     requirements={"id"="\d+"}
     * )
     * @Rest\View(StatusCode = 204)
     * @IsGranted("ROLE_USER")
     */
    public function deleteUserMethod(User $user)
    {
        $this->em->remove($user);
        $this->em->flush();
    }
}
