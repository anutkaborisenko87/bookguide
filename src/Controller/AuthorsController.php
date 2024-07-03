<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Author;
use App\Exception\ValidationException;
use App\Service\AuthorsServiceInterface;
use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Model\AuthorsListResponse;
use Nelmio\ApiDocBundle\Annotation\Model;
class AuthorsController extends AbstractController
{
    private $authorsService;
    public function __construct(AuthorsServiceInterface $authorsService)
    {
        $this->authorsService = $authorsService;
    }

    /**
     * @Route("/api/v1/authors", methods={"GET"})
     * @OA\Get(
     *     path="/api/v1/authors",
     *     summary="Route to view a list of all authors",
     *     description="View a list of all authors. The result with pagination",
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="The number of the page",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=1
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Count athors on a page",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=10
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Return array",
     *         @OA\JsonContent(ref=@Model(type=AuthorsListResponse::class))
     *     ),
     *     tags={"Authors"}
     * )
     */
   final public function index(Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);
        return $this->json($this->authorsService->getAuthors($page, $limit));
    }
    /**
     * @Route("/api/v1/authors", methods={"POST"})
     * @OA\Post(
     *     path="/api/v1/authors",
     *     summary="Route to create new author",
     *      description="Create new author",
     *     @OA\RequestBody(
     *           description="Book Payload",
     *           required=true,
     *           @OA\MediaType(
     *               mediaType="multipart/form-data",
     *               @OA\Schema(
     *                   required={"first_name", "last_name"},
     *                   @OA\Property(
     *                       property="first_name",
     *                       description="Author`s first name",
     *                       type="string"
     *                   ),
     *                   @OA\Property(
     *                       property="last_name",
     *                       description="Author`s last name",
     *                       type="string"
     *                   ),
     *                   @OA\Property(
     *                       property="patronomic_name",
     *                       description="Author`s patronomic name",
     *                       type="string"
     *                   )
     *               )
     *           ),
     *       ),
     *     @OA\Response(
     *       response=200,
     *       description="Return array"
     *     ),
     *     @OA\Response(
     *           response=400,
     *           description="Bad request. The book was not created due to validation errors or internal server error."
     *       ),
     *     tags={"Authors"}
     * )
     */
    final public function createAuthor(Request $request): JsonResponse
    {
        try {
            $author = $this->authorsService->createAuthor(
                $request->get('first_name'),
                $request->get('last_name'),
                $request->get('patronomic_name'),
            );
            return $this->json(['success' => 'Author created successfully', 'author' => $author]);
        } catch (ValidationException $e) {
            return new JsonResponse(['error' => $e->getMessage(), 'errors' => $e->getErrors()], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
