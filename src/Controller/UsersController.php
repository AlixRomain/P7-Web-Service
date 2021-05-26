<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\User;
use App\Exception\Errors;
use App\Exception\ResourceValidationException;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Model;

class UsersController extends AbstractFOSRestController
{
    private $em;
    private $repoUser;
    private $errors;

    public function __construct(EntityManagerInterface $em, UserRepository $repoUser,  Errors $errors)
    {
        $this->em          = $em;
        $this->repoUser = $repoUser;
        $this->errors = $errors;
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

    /*/**
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
   /* public function getUserList(): array
    {
        return $this->repoUser->findBy(['client' => $this->getUser()->getClient()]);
    }*/

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
     *          description="ID du user to show",
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
     * Add one user by client
     * @Rest\Post(
     *     path = "/api/user",
     *     name = "add_user",
     * )
     * @Rest\View(StatusCode = 201, serializerGroups={"MediumUser"})
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
     *                     property="fullname",
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
     *                 example={"fullname":"Martin Dupont","email":"martin@dupont.com","password":"OpenClass21!","age": 48}
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
    public function postAddOneUser(User $user, ConstraintViolationList $violations, UserPasswordEncoderInterface $encoder)
    {
        if(false !== array_search('ROLE_ADMIN',$this->getUser()->getRoles())){
            $data= 'ADMIN must use the path /api/user/{id} to associate a new user with a customer' ;
            return new JsonResponse($data, Response::HTTP_FORBIDDEN);
        }
        $this->errors->violation($violations);
        $user->setPassword($encoder->encodePassword($user, $user->getPassword()));
        $user->setClient($this->getUser()->getClient());
        $user->setCreatedAt(new \DateTime());
        $this->em->persist($user);
        $this->em->flush();
        return $this->view(
            $user,
            Response::HTTP_CREATED
        );
    }

    /**
     * Associate one user to customer by Admin
     * @Rest\Post(
     *     path = "/api/admin/user/{id}",
     *     name = "add_user_by_admin",
     * )
     * @Rest\View(StatusCode = 201,serializerGroups={"MediumUser"})
     * @ParamConverter(
     *     "user",
     *      converter="fos_rest.request_body",
     *      options={
     *         "validator" = {"groups" = "Create"}
     *     }
     * )
     *
     * @param User                         $user
     * @param Client                       $client
     * @param ConstraintViolationList      $violations
     * @param UserPasswordEncoderInterface $encoder
     *
     * @return View
     * @throws ResourceValidationException
     * @IsGranted("ROLE_ADMIN")
     * @OA\Tag(name="User")
     * @OA\Post(
     *     path="/api/admin/user/{id}",
     *     summary="Associate one user to customer by Admin",
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="ID to customer",
     *          required=true
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="fullname",
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
     *                 example={"fullname":"Martin Dupont","email":"martin@dupont.com","password":"OpenClass21!","age": 48}
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
    public function postAddOneUserByAdmin(User $user, Client $client, ConstraintViolationList $violations, UserPasswordEncoderInterface $encoder)
    {
        $this->errors->violation($violations);
        $user->setPassword($encoder->encodePassword($user, $user->getPassword()));
        $user->setUsername($user->getUsername());
        $user->setClient($client);
        $user->setCreatedAt(new \DateTime());
        $this->em->persist($user);
        $this->em->flush();
        return $this->view(
            $user,
            Response::HTTP_CREATED
        );
    }

    /**
     * Update one user by admin
     * @Rest\Put(
     *     path = "/api/user/{id}",
     *     name = "update_user",
     * )
     * @Rest\View(StatusCode = 201, serializerGroups={"MediumUser"})
     * @ParamConverter(
     *     "newUser",
     *      converter="fos_rest.request_body",
     *      options={
     *         "validator" = {"groups" = "Update"}
     *     }
     * )
     *
     * @param User                    $userr
     * @param User                    $newUser
     * @param ConstraintViolationList $violations
     * @param Request                 $request
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
     *                     property="fullname",
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
     *                 example={"fullname":"Martin Dupont","age": 48}
     *             )
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Id user to update",
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
    public function putUpdateOneUser(User $userr, User $newUser, ConstraintViolationList $violations, Request $request): View
    {

        $data = json_decode($request->getContent(),true);
        $this->errors->violation($violations);
        if(array_key_exists('fullname', $data)){$userr->setFullname($newUser->getFullname());}
        if(array_key_exists('age', $data)){$userr->setAge($newUser->getAge());}
        $this->em->flush();
        return $this->view(
            $userr,
            Response::HTTP_CREATED
        );
    }

    /**
     * Delete one user by admin
     * @Rest\Delete(
     *     path = "/api/user/{id}",
     *     name = "delete_user",
     *     requirements={"id"="\d+"}
     * )
     *
     * @param User $userr
     *
     * @return JsonResponse
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
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
