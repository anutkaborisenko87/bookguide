<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\ValidationException;
use App\Model\BookListResponse;
use App\Model\FoundedBookListResponse;
use App\Model\BookDetails;
use App\Service\BooksService;
use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
     *     summary="Route to view a list of all books",
     *    description="View a list of all books. The result with pagination",
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
    final public function index(Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);
        return $this->json($this->booksService->getAllBooks($page, $limit));
    }

    /**
     * @Route("/api/v1/books/search", methods={"GET"})
     * @OA\Get(
     *     path="/api/v1/books/search",
     *     summary="Route to view a list of all books",
     *      description="View a list of all books. The result with pagination",
     *     @OA\Parameter(
     *          name="authorName",
     *          in="query",
     *          description="The number of the page",
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *     @OA\Response(
     *       response=200,
     *       description="Return array",
     *     @OA\JsonContent(ref=@Model(type=FoundedBookListResponse::class))
     *     ),
     *     tags={"Books"}
     * )
     */
    final public function searchBooks(Request $request): JsonResponse
    {
        try {
            $search = $request->query->get('authorName');
            $books = $this->booksService->getBooksByAuthorName($search);
            return $this->json($books);
        } catch (ValidationException $e) {
            return new JsonResponse(['error' => $e->getMessage(), 'errors' => $e->getErrors()], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * @Route("/api/v1/books/{id}", methods={"GET"} , name="get_book",
     *      requirements={"id"="\d+"})
     * @OA\Get(
     *      path="/api/v1/books/{id}",
     *     summary="Route to view one book details",
     *     description="View one book details",
     *     @OA\Parameter(
     *              name="id",
     *              in="path",
     *              required=true,
     *              description="Book id to show",
     *              @OA\Schema(
     *                  type="integer"
     *              )
     *          ),
     *      @OA\Response(
     *        response=200,
     *        description="Return book details",
     *      @OA\JsonContent(ref=@Model(type=BookDetails::class))
     *      ),
     *     @OA\Response(
     *         response=404,
     *         description="Not found response"
     *       ),
     *      tags={"Books"}
     *  )
     */
    final public function showBookDetails(int $id): Response
    {
        return $this->json($this->booksService->getBookById($id));
    }

    /**
     * @Route("/api/v1/books", methods={"POST"}, name="add_book")
     *
     * @OA\Post(
     *      path="/api/v1/books",
     *      summary="Route to create a new book",
     *      description="Create a new book",
     *      operationId="createBook",
     *      tags={"Books"},
     *      @OA\RequestBody(
     *          description="Book Payload",
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  required={"title", "authors", "image"},
     *                  @OA\Property(
     *                      property="title",
     *                      description="Book title",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="description",
     *                      description="Book description",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="authors",
     *                      description="Ids of authors",
     *                      type="array",
     *                      @OA\Items(type="integer")
     *                  ),
     *                  @OA\Property(
     *                      property="image",
     *                      description="Book image",
     *                      type="string",
     *                      format="binary"
     *                  ),
     *                  @OA\Property(
     *                      property="published_at",
     *                      description="Published at",
     *                      type="string"
     *                  )
     *              )
     *          ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Book created successfully",
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad request. The book was not created due to validation errors or internal server error."
     *      )
     *  )
     */
    final public function createBook(Request $request): JsonResponse
    {
        try {
            $book = $this->booksService->createBook(
                $request->get('title'),
                $request->get('description'),
                $request->get('authors'),
                $request->files->get('image'),
                $request->get('published_at')
            );
            return $this->json(['success' => 'Book created successfully', 'book' => $book]);
        } catch (ValidationException $e) {
            return new JsonResponse(['error' => $e->getMessage(), 'errors' => $e->getErrors()], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * @Route("/api/v1/books/{id}", methods={"POST"}, name="update_book",
     *      requirements={"id"="\d+"})
     *
     * @OA\Post(
     *      path="/api/v1/books/{id}",
     *      summary="Route to update book",
     *      description="Update book",
     *      operationId="updateBook",
     *      tags={"Books"},
     *     @OA\Parameter(
     *               name="id",
     *               in="path",
     *               required=true,
     *               description="Book id to update",
     *               @OA\Schema(
     *                   type="integer"
     *               )
     *           ),
     *      @OA\RequestBody(
     *          description="Book Payload",
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="title",
     *                      description="Book title",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="description",
     *                      description="Book description",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="authors",
     *                      description="Ids of authors",
     *                      type="array",
     *                      @OA\Items(type="integer")
     *                  ),
     *                  @OA\Property(
     *                      property="image",
     *                      description="Book image",
     *                      type="string",
     *                      format="binary"
     *                  ),
     *                  @OA\Property(
     *                      property="published_at",
     *                      description="Published at",
     *                      type="string"
     *                  )
     *              )
     *          ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Book created successfully",
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad request. The book was not created due to validation errors or internal server error."
     *      )
     *  )
     */
    final public function updateBook(Request $request, int $id): JsonResponse
    {
        try {
            $book = $this->booksService->updateBook(
                $id,
                $request->get('title'),
                $request->get('description'),
                $request->get('authors'),
                $request->files->get('image'),
                $request->get('published_at')
            );
            return $this->json(['success' => 'Book updated successfully', 'book' => $book]);
        } catch (ValidationException $e) {
            return new JsonResponse(['error' => $e->getMessage(), 'errors' => $e->getErrors()], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * @Route("/api/v1/books/{id}", methods={"DELETE"}, name="delete_book",
     *      requirements={"id"="\d+"})
     *
     * @OA\Delete(
     *      path="/api/v1/books/{id}",
     *      summary="Route to delete book",
     *      description="Delete book",
     *      operationId="deleteBook",
     *      tags={"Books"},
     *     @OA\Parameter(
     *               name="id",
     *               in="path",
     *               required=true,
     *               description="Book id to update",
     *               @OA\Schema(
     *                   type="integer"
     *               )
     *           ),
     *      @OA\Response(
     *          response=201,
     *          description="Book created successfully",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Book not found"
     *      )
     *  )
     */
    final public function deleteBook(int $id): JsonResponse
    {
        $book = $this->booksService->deleteteBook($id);
        return $this->json(['success' => 'Book deleted successfully', 'book' => $book]);
    }
}
