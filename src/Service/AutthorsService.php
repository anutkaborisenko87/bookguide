<?php

namespace App\Service;

use App\Entity\Author;
use App\Entity\Book;
use App\Model\AuthorListItem;
use App\Model\AuthorsBooksListItem;
use App\Model\AuthorsListResponse;
use App\Repository\AuthorRepository;

class AutthorsService implements AuthorsServiceInterface
{
    private $authorRepository;
    public function __construct(AuthorRepository $authorRepository)
    {
        $this->authorRepository = $authorRepository;
    }

    final public function getAuthors(int $page, ?int $limit = 10): AuthorsListResponse
    {
        $offset = ($page - 1) * $limit;
        $totalAuthors = $this->authorRepository->count([]);
        $totalPages = ceil((float)$totalAuthors / $limit);
        $authors = $this->authorRepository->findBy([], null, $limit, $offset);
        $items = array_map(function (Author $author) {
            $books = array_map(
                fn(Book $book) => new AuthorsBooksListItem(
                    $book->getId(),
                    $book->getTitle(),
                    $book->getImage()
                ),
                $author->getBooks()->toArray()
            );

            return new AuthorListItem(
                $author->getId(),
                $author->getFirstName(),
                $author->getLastName(),
                $author->getPatronomicName(),
                $books
            );
        }, $authors);
        return new AuthorsListResponse($items, $page, $totalPages, $limit);
    }
}
