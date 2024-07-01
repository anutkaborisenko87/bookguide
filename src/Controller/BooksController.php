<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\BookListResponse;
use App\Service\BooksService;
use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Model;

class BooksController extends AbstractController
{
    private $booksService;

    public function __construct(BooksService $booksService)
    {
        $this->booksService = $booksService;
    }

    /**
     * @Route("/api/v1/books", methods={"GET"})
     * @OA\Get(
     *     path="/api/v1/books",
     *     @OA\Parameter(
     *          name="page",
     *          in="query",
     *          description="The number of the page",
     *          required=false,
     *          @OA\Schema(
     *              type="integer",
     *              default=1
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="limit",
     *          in="query",
     *          description="Count books on a page",
     *          required=false,
     *          @OA\Schema(
     *              type="integer",
     *              default=10
     *          )
     *      ),
     *     @OA\Response(
     *       response=200,
     *       description="Return array",
     *     @OA\JsonContent(ref=@Model(type=BookListResponse::class))
     *     ),
     *     tags={"Books"}
     * )
     */
    public function index(Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);
        return $this->json($this->booksService->getAllBooks($page, $limit));
    }
}
