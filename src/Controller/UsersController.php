<?php

namespace App\Controller;

use App\Entity\User;
use App\Exception\ResourceValidationException;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Model;

class UsersController extends AbstractFOSRestController
{
    private $em;
    private $repoUser;

    public function __construct(EntityManagerInterface $em, UserRepository $repoUser)
    {
        $this->em          = $em;
        $this->repoUser = $repoUser;
    }
    /**
     * @Rest\Post (
     *     path = "/api/login_check",
     *     name = "login_check",
     * )
     * @OA\POST(
     *      path="/api/login_check",
     *      tags={"Authentication"},
     *      summary="Logged with email and password",
     *      operationId="login",
     *     @OA\RequestBody(
     *          description="Add a credential at json format",
     *          required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                  type="string",
     *                  title="Login field",
     *                  @OA\Property(property="email", ref=@Model(type=User::class)),
     *                  @OA\Property(property="password", ref=@Model(type=User::class)),
     *                  example={"email":"admin@admin.com", "password":"OpenClass21!"}
     *             )
     *          )
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Token OK",
     *       @OA\JsonContent(type="token", type="string")
     *      )
     * )
     * @OA\Response(
     *     response=400,
     *     description="Bad request - Invalid JSON",
     * )
     * @OA\Response(
     *     response=401,
     *     description="Bad credentials",
     * )
     *
     *
     */
    public function checkAction(){
    }

    /**
     * Show a customer list from one clients coporation
     *
     * @Rest\Get(
     *     path = "/api/users",
     *     name = "all_users_show"
     * )
     * @Rest\View(serializerGroups={})
     * @IsGranted("ROLE_USER")
     * @OA\Tag(name="User")
     */
    public function getUserList(): array
    {
        return $this->repoUser->findBy(['client' => $this->getUser()->getClient()]);
    }

    /**
     * Show one user details
     * @Rest\Get(
     *     path = "/api/users/{id}",
     *     name = "user_show",
     *     requirements={"id"="\d+"}
     * )
     * @Rest\View(serializerGroups={"MediumUser"})
     * @IsGranted("ROLE_USER")
     * @Security("userr.getClient() === user.getClient() || is_granted('ROLE_ADMIN')")
     *
     * @param User $userr
     *
     * @return User
     * @OA\Tag(name="User")
     * @OA\Get(
     *      path = "/api/users/{id}",
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="ID de la resource",
     *          required=true
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Show a user detail",
     *       @OA\JsonContent(
     *          type="array",
     *           @OA\Items(ref=@Model(type=User::class, groups={"MediumUser"}))
     *      )
     *    )
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
    public function getOneUser(User $userr): User
    {
        return $userr;
    }
    /**
     * Show all users from every customers corporation
     * @Rest\Get(
     *     path = "/api/admin/users",
     *     name = "all_users_show_admin"
     * )
     * @Rest\View(serializerGroups={"MediumUser", "clientUser"})
     * @IsGranted("ROLE_ADMIN")
     * @OA\Tag(name="User")
     * @OA\Get(
     *      path = "/api/admin/users",
     *     @OA\Response(
     *       response="200",
     *       description="Show a users list of BileMo Database",
     *       @OA\JsonContent(
     *          type="array",
     *          @OA\Items(ref=@Model(type=User::class, groups={"MediumUser", "clientUser"}))
     *       )
     *    )
     * )
     * @OA\Response(
     *     response=401,
     *     description="UNAUTHORIZED - JWT Token not found | Expired JWT Token | Invalid JWT Token"
     * )
     */
    public function getAllUsersOfBileMo(): array
    {
        return $this->repoUser->findAll();
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
     * @OA\Tag(name="User")
     * @OA\Post(
     *     path="/api/user",
     *     summary="Add one user by client",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="username",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="email",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="age",
     *                     type="integer"
     *                 ),
     *                 example={"username":"Martin Dupont","email":"martin@dupont.com","password":"OpenClass21!","age": 48}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *        response=201,
     *        description="CREATED",
     *        @OA\JsonContent(
     *           type="array",
     *           @OA\Items(ref="#/components/schemas/User")),
     *        @OA\Schema(
     *          type="array",
     *          @OA\Items(ref=@Model(type=User::class))
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
    public function postAddOneUser(User $user, ConstraintViolationList $violations, UserPasswordEncoderInterface $encoder): View
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
        }
        $user->setPassword($encoder->encodePassword($user, $user->getPassword()));
        $user->setClient($this->getUser()->getClient());
        $user->setCreatedAt(new \DateTime());
        $this->em->persist($user);
        $this->em->flush();
        return $this->view(
            $user,
            Response::HTTP_CREATED,
            [
                'Location' => $this->generateUrl('user_show', ['id' => $user->getId()])
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
     *     "newUser",
     *      converter="fos_rest.request_body",
     *      options={
     *         "validator" = {"groups" = "Create"}
     *     }
     * )
     *
     * @param User                    $userr
     * @param User                    $newUser
     * @param ConstraintViolationList $violations
     *
     * @return View
     * @throws ResourceValidationException
     * @Security("userr.getClient() === user.getClient() || is_granted('ROLE_ADMIN')")
     * @IsGranted("ROLE_USER")
     * @OA\Tag(name="User")
     * @OA\Put(
     *     path="/api/user/{id}",
     *     summary="Update one user by client",
     *      @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="username",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="email",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="age",
     *                     type="integer"
     *                 ),
     *                 example={"username":"Martin Dupont","email":"martin@dupont.com","password":"OpenClass21!","age": 48}
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
     *        @OA\JsonContent(type="array",  @OA\Items(ref=@Model(type=User::class, groups={"MediumUser"}))),
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
    public function putUpdateOneUser(User $userr, User $newUser, ConstraintViolationList $violations): View
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
        $userr->setUsername($newUser->getUsername());
        $userr->setAge($newUser->getAge());
        $userr->setEmail($newUser->getEmail());
        $this->em->persist($userr);
        $this->em->flush();
        return $this->view(
            $userr,
            Response::HTTP_CREATED,
            [
                'Location' => $this->generateUrl('user_show', ['id' => $userr->getId()])
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
     * @param User $userr
     * @Rest\View(StatusCode = 204)
     * @Security("userr.getClient() === user.getClient() || is_granted('ROLE_ADMIN')")
     * @Security ("userr !== user")
     * @IsGranted("ROLE_USER")
     * @OA\Tag(name="User")
     * @OA\Delete(
     *     path="/api/user/{id}",
     *     summary="Delete one user by client",
     *     description="DELETE",
     *     operationId="delete Mobile",
     *     @OA\Parameter(
     *         description="User id to delete",
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
    public function deleteUserMethod(User $userr)
    {
        $this->em->remove($userr);
        $this->em->flush();
    }
}
