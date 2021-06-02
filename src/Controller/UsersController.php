<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\User;
use App\Exception\Errors;
use App\Exception\ResourceValidationException;
use App\Repository\UserRepository;
use App\Service\Pagination;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use JMS\Serializer\Expression\ExpressionEvaluator;
use JMS\Serializer\SerializerBuilder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
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
    private $errors;

    public function __construct(EntityManagerInterface $em,  Errors $errors)
    {
        $this->em          = $em;
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
     * Show every users from one customer
     * @Rest\Get(
     *     path = "/api/users",
     *     name = "all_users_show_admin"
     * )
     * @Rest\View(serializerGroups={"MediumUser", "clientUser"})
     * @IsGranted("ROLE_USER")
     * @OA\Tag(name="User")
     * @OA\Get(
     *      path = "/api/users",
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
    public function getAllUsersByCustomer(ParamFetcherInterface $paramFetcher, Pagination $pagination): Response
    {
        $repo = $this->getDoctrine()->getRepository(User::class);
        $route = 'all_users_show_admin';
        $param = $this->getUser()->getClient();
        $paginatedCollection = $pagination->getViewPaginate($repo,$paramFetcher,$route,$param);

        $view = $this->view(
            $paginatedCollection,
            Response::HTTP_OK
        );
        return $this->handleView($view);
    }

    /**
     * Show every users from one customer
     * @Rest\Get(
     *     path = "/api/Admin/users-customer/{id}",
     *     name = "all_users_customer_show_admin"
     * )
     *
     * @param Client $client
     * @Rest\View(serializerGroups={"MediumUser", "clientUser"})
     * @IsGranted("ROLE_ADMIN")
     * @OA\Tag(name="User")
     * @OA\Get(
     *      path = "/api/Admin/users-customer/{id}",
     *     @OA\Response(
     *       response="200",
     *       description="Show a users list of one customer",
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
    public function getAllUsersByCustomerByAdmin(Client $client, ParamFetcherInterface $paramFetcher, Pagination $pagination): Response
    {
        $repo = $this->getDoctrine()->getRepository(User::class);
        $route = 'all_users_show_admin';
        $param = $client;
        $paginatedCollection = $pagination->getViewPaginate($repo,$paramFetcher,$route,$param);

        $view = $this->view(
            $paginatedCollection,
            Response::HTTP_OK
        );
        return $this->handleView($view);
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
            $data= 'ADMIN must use the path /api/admin/user/{id} to associate a new user with a customer' ;
            return new JsonResponse($data, Response::HTTP_FORBIDDEN);
        }
        $this->errors->violation($violations);
        $user->setUsername($user->getUsername());
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
    public function postAddOneUserByAdmin(User $user, Client $client, ConstraintViolationList $violations, UserPasswordEncoderInterface $encoder): View
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
