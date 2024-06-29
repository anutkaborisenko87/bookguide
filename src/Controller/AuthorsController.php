<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Author;
use App\Service\AuthorsServiceInterface;
use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
     *     path="/api/v1/authors/add",
     *     @OA\Response(
     *       response=200,
     *       description="Return array"
     *     ),
     *     tags={"Authors"}
     * )
     */
    public function createAuthor()
    {

    }
}
