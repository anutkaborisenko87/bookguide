<?php

declare(strict_types=1);

namespace App\Controller;

use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BooksController extends AbstractController
{

    /**
     * @Route("/api/v1/books", methods={"GET"})
     * @OA\Get(
     *     path="/api/v1/books",
     *     @OA\Response(
     *       response=200,
     *       description="Return array"
     *     ),
     *     tags={"Books"}
     * )
     */
    public function index(): Response
    {
        return $this->json(['test' => 'books']);
    }
}
